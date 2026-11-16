<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthRateLimit;
use App\Support\AuthTokenService;
use App\Support\SafeInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthTokenController extends Controller
{
    public function __construct(private AuthTokenService $tokens) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $email = mb_strtolower(SafeInput::plainText($data['email']));
        $ip = (string) $request->ip();
        $blocked = AuthRateLimit::loginBlocked($email, $ip);

        if ($blocked['blocked']) {
            return response()->json([
                'message' => $blocked['message'],
            ], 429);
        }

        /** @var User|null $user */
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            AuthRateLimit::hitLoginFailure($email, $ip);

            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        AuthRateLimit::clearLoginFailures($email, $ip);

        $pair = $this->tokens->issuePair($user, $request);
        $request->attributes->set('activity_actor', $user);

        return response()->json([
            'message' => 'Logged in.',
            'access_token' => $pair['access_token'],
            'refresh_token' => $pair['refresh_token'],
            'token_type' => $pair['token_type'],
            'expires_in' => $pair['expires_in'],
            'user' => $this->userPayload($pair['user']),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string', 'min:40', 'max:200'],
        ]);

        $pair = $this->tokens->refresh($data['refresh_token'], $request);
        $request->attributes->set('activity_actor', $pair['user']);

        return response()->json([
            'message' => 'Token refreshed.',
            'access_token' => $pair['access_token'],
            'refresh_token' => $pair['refresh_token'],
            'token_type' => $pair['token_type'],
            'expires_in' => $pair['expires_in'],
            'user' => $this->userPayload($pair['user']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $refresh = $request->input('refresh_token');
        $this->tokens->revokeAccess($user);
        $this->tokens->revokeRefresh(is_string($refresh) ? $refresh : null);

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->tokens->revokeAll($user);

        return response()->json([
            'message' => 'Logged out from all devices.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->full_name ?: $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }
}
