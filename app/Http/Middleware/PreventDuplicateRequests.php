<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateRequests
{
    private const RATE_MAX_PER_MINUTE = 45;

    private const DEDUPE_SECONDS = 4;

    /** @var list<string> */
    private const MUTATING = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), self::MUTATING, true)) {
            return $next($request);
        }

        $actor = Auth::check()
            ? 'user:'.Auth::id()
            : 'ip:'.$request->ip();

        $rateKey = 'mutate-rate:'.$actor;

        if (RateLimiter::tooManyAttempts($rateKey, self::RATE_MAX_PER_MINUTE)) {
            return $this->tooMany($request, RateLimiter::availableIn($rateKey), 'Too many requests. Please wait a moment and try again.');
        }

        RateLimiter::hit($rateKey, 60);

        $fingerprint = $this->fingerprint($request, $actor);
        $dedupeKey = 'mutate-dedupe:'.$fingerprint;

        if (! Cache::add($dedupeKey, 1, self::DEDUPE_SECONDS)) {
            return $this->tooMany($request, self::DEDUPE_SECONDS, 'Duplicate request ignored. Please wait a moment.');
        }

        return $next($request);
    }

    private function fingerprint(Request $request, string $actor): string
    {
        $payload = $request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'new_password_confirmation',
        ]);

        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                $payload[$key] = collect($file)->map(function ($item) {
                    return $item ? ($item->getClientOriginalName().':'.$item->getSize()) : null;
                })->all();
            } elseif ($file) {
                $payload[$key] = $file->getClientOriginalName().':'.$file->getSize();
            }
        }

        ksort($payload);

        return hash('sha256', implode('|', [
            $actor,
            $request->method(),
            $request->path(),
            json_encode($payload),
        ]));
    }

    private function tooMany(Request $request, int $retryAfter, string $message): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', (string) $retryAfter);
        }

        return back()
            ->withInput()
            ->with('status', $message)
            ->with('profile_update_error', $message);
    }
}
