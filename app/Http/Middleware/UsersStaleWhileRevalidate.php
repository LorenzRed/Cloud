<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UsersStaleWhileRevalidate
{
    /** @var list<string> */
    private const NO_STORE_PATHS = [
        'users/verification',
        'users/profile/bhw-chat',
        'users/profile/chat-support',
        'users/session/csrf',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET')) {
            return $response;
        }

        if (Auth::user()?->role !== 'users') {
            return $response;
        }

        if (! $request->is('users', 'users/*')) {
            return $response;
        }

        foreach (self::NO_STORE_PATHS as $path) {
            if ($request->is($path)) {
                $response->headers->set('Cache-Control', 'private, no-store');

                return $response;
            }
        }

        $response->headers->set(
            'Cache-Control',
            'private, max-age=0, stale-while-revalidate=120'
        );

        return $response;
    }
}
