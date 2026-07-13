<?php

namespace App\Support;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

class AuthTokenService
{
    public const ACCESS_TOKEN_NAME = 'api-access';

    public function accessTtlMinutes(): int
    {
        return max(1, (int) config('auth_tokens.access_ttl_minutes', 15));
    }

    public function refreshTtlMinutes(): int
    {
        return max(60, (int) config('auth_tokens.refresh_ttl_minutes', 60 * 24 * 30));
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int, user: User}
     */
    public function issuePair(User $user, ?Request $request = null): array
    {
        $user->tokens()->where('name', self::ACCESS_TOKEN_NAME)->delete();

        $access = $this->createAccessToken($user);
        $refreshPlain = $this->createRefreshToken($user, $request);

        return $this->formatPair($user, $access, $refreshPlain);
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int, user: User}
     */
    public function refresh(string $refreshPlain, ?Request $request = null): array
    {
        $hash = hash('sha256', $refreshPlain);

        /** @var RefreshToken|null $stored */
        $stored = RefreshToken::query()
            ->where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->first();

        if (! $stored || ! $stored->isValid()) {
            abort(401, 'Invalid or expired refresh token.');
        }

        $user = $stored->user;
        if (! $user) {
            abort(401, 'Invalid or expired refresh token.');
        }

        $stored->forceFill(['revoked_at' => now()])->save();

        $user->tokens()->where('name', self::ACCESS_TOKEN_NAME)->delete();

        $access = $this->createAccessToken($user);
        $newRefresh = $this->createRefreshToken($user, $request);

        return $this->formatPair($user, $access, $newRefresh);
    }

    public function revokeAccess(User $user): void
    {
        $user->tokens()->where('name', self::ACCESS_TOKEN_NAME)->delete();
    }

    public function revokeRefresh(?string $refreshPlain): void
    {
        if (! filled($refreshPlain)) {
            return;
        }

        RefreshToken::query()
            ->where('token_hash', hash('sha256', $refreshPlain))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public function revokeAll(User $user): void
    {
        $this->revokeAccess($user);
        RefreshToken::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    private function createAccessToken(User $user): NewAccessToken
    {
        return $user->createToken(
            self::ACCESS_TOKEN_NAME,
            ['*'],
            Carbon::now()->addMinutes($this->accessTtlMinutes())
        );
    }

    private function createRefreshToken(User $user, ?Request $request = null): string
    {
        $plain = Str::random(64);

        RefreshToken::query()->create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => Carbon::now()->addMinutes($this->refreshTtlMinutes()),
            'user_agent' => $request?->userAgent(),
            'ip_address' => $request?->ip(),
        ]);

        return $plain;
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int, user: User}
     */
    private function formatPair(User $user, NewAccessToken $access, string $refreshPlain): array
    {
        return [
            'access_token' => $access->plainTextToken,
            'refresh_token' => $refreshPlain,
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTtlMinutes() * 60,
            'user' => $user,
        ];
    }
}
