<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces role for role-prefixed URLs so a forgotten inline check cannot open another role's area.
 * Shared routes (dashboard, profile-picture, verification-id, chat attachments) are skipped.
 */
class EnsureRoleByPrefix
{
    /**
     * Longer prefixes first.
     *
     * @var list<array{0: string, 1: list<string>}>
     */
    private const PREFIX_ROLES = [
        ['bhw-head', [User::ROLE_BHW_HEAD]],
        ['barangay-captain', [User::ROLE_BRG_CAPTAIN]],
        ['bhw', [User::ROLE_BHW]],
        ['bns', [User::ROLE_BNS]],
        ['lgu', [User::ROLE_LGU]],
        ['users', [User::ROLE_USERS]],
    ];

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        foreach (self::PREFIX_ROLES as [$prefix, $roles]) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                if (! in_array((string) $user->role, $roles, true)) {
                    abort(403, 'Unauthorized for this area.');
                }

                break;
            }
        }

        return $next($request);
    }
}
