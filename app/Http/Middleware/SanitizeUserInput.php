<?php

namespace App\Http\Middleware;

use App\Support\SafeInput;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeUserInput
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->query->replace(
            SafeInput::sanitizePayload($request->query->all())
        );

        if ($request->isJson()) {
            $request->json()->replace(
                SafeInput::sanitizePayload($request->json()->all())
            );
        }

        $request->request->replace(
            SafeInput::sanitizePayload($request->request->all())
        );

        return $next($request);
    }
}
