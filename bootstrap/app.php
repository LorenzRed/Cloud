<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->web(append: [
            \App\Http\Middleware\SanitizeUserInput::class,
            \App\Http\Middleware\PreventDuplicateRequests::class,
            \App\Http\Middleware\UsersStaleWhileRevalidate::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SanitizeUserInput::class,
            \App\Http\Middleware\PreventDuplicateRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {
            $path = trim($request->path(), '/');

            if (preg_match('#^bhw/residents/(\d+)/monitoring-cases$#', $path, $matches)) {
                return redirect()->route('bhw.residents.profile', [
                    'residentRequest' => $matches[1],
                    'tab' => 'maintenance',
                ]);
            }

            if (preg_match('#^bhw/monitoring-cases/(\d+)/visits$#', $path, $matches)
                && ($case = \App\Models\HouseholdMonitoringCase::query()->find($matches[1]))
                && $case->resident_request_id) {
                return redirect()->route('bhw.residents.profile', [
                    'residentRequest' => $case->resident_request_id,
                    'tab' => 'maintenance',
                ]);
            }

            return null;
        });
    })->create();
