<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Shared auth attempt limits (web + API).
 * Email enumeration on register is an intentional UX exception elsewhere.
 */
class AuthRateLimit
{
    /** Failed attempts per email+IP before lockout. */
    public const LOGIN_MAX_ATTEMPTS = 5;

    /** Lockout window after too many login failures (15 minutes). */
    public const LOGIN_DECAY_SECONDS = 900;

    /** Failed attempts from one IP across any emails. */
    public const LOGIN_IP_MAX_ATTEMPTS = 20;

    public const LOGIN_IP_DECAY_SECONDS = 900;

    public static function loginEmailKey(string $email, string $ip): string
    {
        return 'login:'.mb_strtolower(trim($email)).'|'.$ip;
    }

    public static function loginIpKey(string $ip): string
    {
        return 'login-ip:'.$ip;
    }

    /**
     * @return array{blocked: true, seconds: int, message: string}|array{blocked: false}
     */
    public static function loginBlocked(string $email, string $ip): array
    {
        $emailKey = self::loginEmailKey($email, $ip);
        $ipKey = self::loginIpKey($ip);

        if (RateLimiter::tooManyAttempts($emailKey, self::LOGIN_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($emailKey);

            return [
                'blocked' => true,
                'seconds' => $seconds,
                'message' => 'Too many login attempts. Try again in '.$seconds.' seconds.',
            ];
        }

        if (RateLimiter::tooManyAttempts($ipKey, self::LOGIN_IP_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($ipKey);

            return [
                'blocked' => true,
                'seconds' => $seconds,
                'message' => 'Too many login attempts from this network. Try again in '.$seconds.' seconds.',
            ];
        }

        return ['blocked' => false];
    }

    public static function hitLoginFailure(string $email, string $ip): void
    {
        RateLimiter::hit(self::loginEmailKey($email, $ip), self::LOGIN_DECAY_SECONDS);
        RateLimiter::hit(self::loginIpKey($ip), self::LOGIN_IP_DECAY_SECONDS);
    }

    public static function clearLoginFailures(string $email, string $ip): void
    {
        RateLimiter::clear(self::loginEmailKey($email, $ip));
        // Do not clear IP-wide counter on success — still deters sprays.
    }
}
