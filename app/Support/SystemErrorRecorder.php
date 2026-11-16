<?php

namespace App\Support;

use App\Models\SystemError;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class SystemErrorRecorder
{
    public static function record(?Throwable $exception, ?Request $request = null, int $status = 500): void
    {
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
        }
        if ($status < 500 || $request?->attributes->get('system_error_recorded')) {
            return;
        }
        try {
            [$category, $cause, $action] = self::classify($exception);
            $actor = $request?->user() ?? $request?->attributes->get('activity_actor');
            SystemError::create([
                'category' => $category,
                'exception_type' => $exception ? get_class($exception) : null,
                'cause' => $cause,
                'suggested_action' => $action,
                'route_name' => $request?->route()?->getName() ?? $request?->route()?->uri(),
                'method' => $request?->method(),
                'status_code' => $status,
                'user_id' => $actor?->id,
                'actor_name' => $actor?->full_name ?: ($actor?->name ?? ($request ? 'Guest' : 'System / background task')),
                'actor_role' => $actor?->role,
                'source_file' => $exception ? basename($exception->getFile()) : null,
                'source_line' => $exception?->getLine(),
                'created_at' => now(),
            ]);
            $request?->attributes->set('system_error_recorded', true);
        } catch (Throwable) {
            // A database outage must not cause recursive reporting or replace the original error.
            error_log('System error report could not be saved. Check the application error log.');
        }
    }

    private static function classify(?Throwable $exception): array
    {
        $current = $exception;
        while ($current) {
            if ($current instanceof QueryException || $current instanceof \PDOException) {
                return ['Database', 'A database operation failed.', 'Check database availability, migrations and constraints. Use the timestamp to find technical details in the server log.'];
            }
            if ($current instanceof \Illuminate\Http\Client\ConnectionException) {
                return ['Connection', 'The system could not connect to an external service.', 'Check network access and the external service status, then retry.'];
            }
            if ($current instanceof \Illuminate\View\ViewException) {
                return ['Page rendering', 'The system could not render a page.', 'Review the affected page template and the related server log entry.'];
            }
            if ($current instanceof \TypeError || $current instanceof \ErrorException || $current instanceof \Error) {
                return ['Application', 'An application code or data type error interrupted the operation.', 'Review the exception type and source location in the server log.'];
            }
            $current = $current->getPrevious();
        }
        return ['System', 'The server could not complete the operation.', 'Review the server log at this time to determine the exact cause.'];
    }
}
