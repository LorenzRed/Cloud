<?php

namespace App\Http\Middleware;

use App\Models\SystemActivityLog;
use App\Support\SecurityLogRetention;
use App\Support\SystemErrorRecorder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogSystemActivity
{
    public function handle(Request $request, Closure $next): mixed
    {
        $initialErrors = $request->hasSession() ? $request->session()->get('errors') : null;
        $response = null;
        $failure = null;
        try {
            return $response = $next($request);
        } catch (Throwable $exception) {
            $failure = $exception;
            throw $exception;
        } finally {
            $status = $failure instanceof ValidationException ? 422
                : ($failure instanceof HttpExceptionInterface ? $failure->getStatusCode()
                    : ($failure ? 500 : ($response?->getStatusCode() ?? 500)));
            if ($status >= 500) {
                SystemErrorRecorder::record($failure, $request, $status);
            }
            $routeName = $request->route()?->getName() ?? $request->route()?->uri();
            if ($request->isMethod('POST') && in_array($routeName, ['login.perform', 'api/auth/login'], true)) {
                try {
                    $hasErrors = $request->hasSession() && $request->session()->has('errors')
                        && $request->session()->get('errors') !== $initialErrors;
                    $actor = $request->attributes->get('activity_actor') ?? $request->user();
                    $successful = $actor && $status < 400 && ! $hasErrors;
                    $email = $request->input('email');
                    SystemActivityLog::create([
                        'user_id' => $successful ? $actor->id : null,
                        'actor_name' => $successful ? ($actor->full_name ?: $actor->name) : 'Unverified login attempt',
                        'actor_role' => $successful ? $actor->role : null,
                        'action' => $successful ? 'Login successful' : 'Login unsuccessful',
                        'event_type' => $successful ? 'login_success' : 'login_failed',
                        'route_name' => $routeName,
                        'method' => $request->method(),
                        'targets' => [],
                        'ip_address' => $request->ip(),
                        'attempted_email' => is_string($email) ? mb_substr(mb_strtolower(trim($email)), 0, 255) : null,
                        'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                        'status_code' => $status,
                        'outcome' => $successful ? 'completed' : ($status >= 500 ? 'error' : 'rejected'),
                        'created_at' => now(),
                    ]);
                    SecurityLogRetention::purgeIfDue();
                } catch (Throwable $loggingFailure) {
                    Log::warning('Unable to save login monitoring entry.', ['exception_type' => get_class($loggingFailure)]);
                }
            }
        }
    }
}
