<?php

namespace App\Http\Middleware;

use App\Models\SystemActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogSystemActivity
{
    public function handle(Request $request, Closure $next): mixed
    {
        $actor = $request->user();
        $initialErrors = $request->hasSession() ? $request->session()->get('errors') : null;
        $response = null;
        $failure = null;
        try {
            return $response = $next($request);
        } catch (Throwable $exception) {
            $failure = $exception;
            throw $exception;
        } finally {
            if ($failure || ($response && $response->getStatusCode() >= 500)) {
                \App\Support\SystemErrorRecorder::record($failure, $request,
                    $response?->getStatusCode() ?? ($failure instanceof ValidationException ? 422 : 500));
            }
            $routeName = $request->route()?->getName() ?? $request->route()?->uri();
            // Background reads and the log viewer itself must not flood the audit history.
            $shouldLog = ! $request->isMethodSafe()
                || ($actor && ! $request->expectsJson() && ! $request->ajax()
                    && ($failure || str_contains((string) $response?->headers->get('Content-Type'), 'text/html'))
                    && ! str_starts_with((string) $routeName, 'lgu.system-logs')
                    && ! str_ends_with((string) $routeName, '.holder-state'));
            if ($shouldLog && $routeName) {
                try {
                    $actor ??= $request->user() ?? $request->attributes->get('activity_actor');
                    $status = $failure instanceof ValidationException ? 422
                        : ($failure instanceof HttpExceptionInterface ? $failure->getStatusCode()
                            : ($failure ? 500 : ($response?->getStatusCode() ?? 500)));
                    $hasErrors = $request->hasSession() && ! $request->isMethodSafe()
                        && (($request->session()->has('errors') && $request->session()->get('errors') !== $initialErrors)
                            || $request->session()->has('profile_update_error'));
                    $targets = [];
                    foreach ($request->route()->parameters() as $key => $value) {
                        // Store record IDs only, never request bodies, tokens, medical data or messages.
                        $id = $value instanceof Model ? $value->getKey() : $value;
                        if (is_scalar($id) && ctype_digit((string) $id)) {
                            $targets[$key] = (string) $id;
                        }
                    }
                    SystemActivityLog::create([
                        'user_id' => $actor?->id,
                        'actor_name' => $actor?->full_name ?: ($actor?->name ?? 'Guest'),
                        'actor_role' => $actor?->role,
                        'action' => ucwords(str_replace(['.', '-', '_'], ' ', $routeName)),
                        'route_name' => $routeName,
                        'method' => $request->method(),
                        'targets' => $targets,
                        'status_code' => $status,
                        'outcome' => $status >= 500 ? 'error' : (($status >= 400 || $hasErrors) ? 'rejected' : 'completed'),
                        'created_at' => now(),
                    ]);
                } catch (Throwable $loggingFailure) {
                    Log::warning('Unable to save system activity log.', ['exception_type' => get_class($loggingFailure)]);
                }
            }
        }
    }
}
