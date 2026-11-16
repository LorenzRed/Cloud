<?php

namespace App\Support;

use App\Mail\RegisterOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class RegisterEmailOtp
{
    public const OTP_MINUTES = 5;

    public const COOLDOWN_SECONDS = 60;

    public const DAILY_MAX_SENDS = 2;

    public const DAILY_WINDOW_SECONDS = 86400;

    /** Max verification emails one IP may request per hour (spam / cost abuse). */
    public const IP_HOURLY_MAX_SENDS = 8;

    public const IP_HOURLY_WINDOW_SECONDS = 3600;

    public static function dailyKey(string $email): string
    {
        return 'register-otp-daily:'.hash('sha256', mb_strtolower(trim($email)));
    }

    public static function cooldownKey(string $email): string
    {
        return 'register-otp-cooldown:'.hash('sha256', mb_strtolower(trim($email)));
    }

    public static function otpCacheKey(string $email): string
    {
        return 'register_otp:'.hash('sha256', mb_strtolower(trim($email)));
    }

    public static function ipHourlyKey(string $ip): string
    {
        return 'register-otp-ip:'.hash('sha256', $ip);
    }

    public static function cooldownRemaining(string $email): int
    {
        $key = self::cooldownKey($email);

        if (! RateLimiter::tooManyAttempts($key, 1)) {
            return 0;
        }

        return max(0, RateLimiter::availableIn($key));
    }

    public static function dailyRemaining(string $email): int
    {
        $key = self::dailyKey($email);

        if (! RateLimiter::tooManyAttempts($key, self::DAILY_MAX_SENDS)) {
            return 0;
        }

        return max(0, RateLimiter::availableIn($key));
    }

    public static function sendsUsed(string $email): int
    {
        return (int) RateLimiter::attempts(self::dailyKey($email));
    }

    public static function sendsLeft(string $email): int
    {
        return max(0, self::DAILY_MAX_SENDS - self::sendsUsed($email));
    }

    /**
     * @return array{ok: true}|array{ok: false, error: string, field: string}
     */
    public static function send(string $email, ?string $ip = null): array
    {
        $email = mb_strtolower(trim($email));

        // Intentional UX exception: reveal taken emails so residents do not wait on OTP for an existing account.
        if (User::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return [
                'ok' => false,
                'field' => 'email',
                'error' => 'This email is already registered. Try logging in instead.',
            ];
        }

        if (filled($ip)) {
            $ipKey = self::ipHourlyKey($ip);
            if (RateLimiter::tooManyAttempts($ipKey, self::IP_HOURLY_MAX_SENDS)) {
                $seconds = RateLimiter::availableIn($ipKey);

                return [
                    'ok' => false,
                    'field' => 'email',
                    'error' => 'Too many verification requests from this network. Try again in '.self::formatWait($seconds).'.',
                ];
            }
        }

        $dailyKey = self::dailyKey($email);
        $cooldownKey = self::cooldownKey($email);

        if (RateLimiter::tooManyAttempts($dailyKey, self::DAILY_MAX_SENDS)) {
            $seconds = RateLimiter::availableIn($dailyKey);

            return [
                'ok' => false,
                'field' => 'email',
                'error' => 'You reached the limit of '.self::DAILY_MAX_SENDS.' verification codes. Try again in '.self::formatWait($seconds).'.',
            ];
        }

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);

            return [
                'ok' => false,
                'field' => 'email',
                'error' => 'Please wait '.$seconds.' second'.($seconds === 1 ? '' : 's').' before requesting another code.',
            ];
        }

        RateLimiter::hit($dailyKey, self::DAILY_WINDOW_SECONDS);
        RateLimiter::hit($cooldownKey, self::COOLDOWN_SECONDS);
        if (filled($ip)) {
            RateLimiter::hit(self::ipHourlyKey($ip), self::IP_HOURLY_WINDOW_SECONDS);
        }

        $otp = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
        $cacheKey = self::otpCacheKey($email);
        Cache::put($cacheKey, Hash::make($otp), now()->addMinutes(self::OTP_MINUTES));

        try {
            Mail::to($email)->send(new RegisterOtpMail($otp, $email));
        } catch (Throwable $e) {
            report($e);
            Cache::forget($cacheKey);

            return [
                'ok' => false,
                'field' => 'email',
                'error' => 'We could not send the verification code. Please try again.',
            ];
        }

        return ['ok' => true];
    }

    public static function clearSendLimits(string $email): void
    {
        RateLimiter::clear(self::dailyKey($email));
        RateLimiter::clear(self::cooldownKey($email));
    }

    public static function formatWait(int $seconds): string
    {
        if ($seconds >= 3600) {
            $hours = (int) ceil($seconds / 3600);

            return $hours.' hour'.($hours === 1 ? '' : 's');
        }

        if ($seconds >= 60) {
            $minutes = (int) ceil($seconds / 60);

            return $minutes.' minute'.($minutes === 1 ? '' : 's');
        }

        return $seconds.' second'.($seconds === 1 ? '' : 's');
    }
}
