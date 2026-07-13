<?php

namespace App\Support;

class SafeInput
{
    public const SEARCH_MAX = 100;

    public const CHAT_MESSAGE_MAX = 500;

    public const AI_PROMPT_MAX = 1000;

    public const TITLE_MAX = 255;

    public const DESCRIPTION_MAX = 2000;

    public const DEFAULT_STRING_MAX = 5000;

    /** @var list<string> */
    private const SKIP_KEYS = [
        '_token',
        'password',
        'password_confirmation',
        'current_password',
        'old_password',
        'captain_password',
        'captain_password_confirmation',
    ];

    /** @var list<string> */
    private const MULTILINE_KEYS = [
        'message',
        'description',
        'note',
        'notes',
        'content',
        'body',
        'comment',
        'remarks',
        'reason',
        'details',
    ];

    /** @var list<string> */
    private const RAW_JSON_KEY_FRAGMENTS = [
        'geojson',
        'geometry',
        'boundary',
        'coordinates',
        'features',
        'properties',
    ];

    /**
     * Recursively sanitize request payload values.
     *
     * @param  array<mixed>  $payload
     * @return array<mixed>
     */
    public static function sanitizePayload(array $payload): array
    {
        $clean = [];

        foreach ($payload as $key => $value) {
            $clean[$key] = self::sanitizeValue($value, is_string($key) ? $key : null);
        }

        return $clean;
    }

    /**
     * Strip null bytes and most control characters from plain text.
     * Keeps tabs/newlines when $allowMultiline is true.
     */
    public static function plainText(?string $value, bool $allowMultiline = false): string
    {
        $text = str_replace("\0", '', (string) $value);
        $text = strip_tags($text);

        if ($allowMultiline) {
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
            $text = preg_replace("/\r\n?/", "\n", $text) ?? '';
            $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? '';
        } else {
            $text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text) ?? '';
        }

        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? '';

        return trim($text);
    }

    /**
     * Normalize a list/search query and escape LIKE wildcards.
     */
    public static function search(?string $value, int $max = self::SEARCH_MAX): string
    {
        $text = mb_substr(self::plainText($value, false), 0, $max);

        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $text);
    }

    public static function displaySearch(?string $value, int $max = self::SEARCH_MAX): string
    {
        return mb_substr(self::plainText($value, false), 0, $max);
    }

    public static function chatMessage(?string $value, int $max = self::CHAT_MESSAGE_MAX): string
    {
        return mb_substr(self::plainText($value, true), 0, $max);
    }

    public static function aiPrompt(?string $value, int $max = self::AI_PROMPT_MAX): string
    {
        return mb_substr(self::plainText($value, true), 0, $max);
    }

    /**
     * @return list<string|\Illuminate\Contracts\Validation\ValidationRule>
     */
    public static function searchRules(int $max = self::SEARCH_MAX): array
    {
        return ['nullable', 'string', 'max:'.$max];
    }

    /**
     * @return list<string|\Illuminate\Contracts\Validation\ValidationRule>
     */
    public static function aiPromptRules(int $max = self::AI_PROMPT_MAX): array
    {
        return ['required', 'string', 'max:'.$max];
    }

    /**
     * @return list<string|\Illuminate\Contracts\Validation\ValidationRule>
     */
    public static function chatMessageRules(bool $required = false, int $max = self::CHAT_MESSAGE_MAX): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:'.$max,
        ];
    }

    /**
     * Basic denylist for obvious prompt-injection / jailbreak attempts.
     * Returns null when OK, or an error message string.
     */
    public static function aiPromptRejectionReason(string $prompt): ?string
    {
        $normalized = mb_strtolower($prompt);
        $patterns = [
            '/ignore (all |any )?(previous|prior|above) (instructions|rules|prompts)/u',
            '/disregard (all |any )?(previous|prior|above) (instructions|rules|prompts)/u',
            '/you are now (dan|jailbreak|unrestricted)/u',
            '/system prompt:/u',
            '/do not follow your (system|developer) (prompt|instructions)/u',
            '/reveal (your )?(system|hidden|developer) (prompt|instructions)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return 'That message looks like an attempt to override the assistant. Please ask a normal health or app question.';
            }
        }

        return null;
    }

    private static function sanitizeValue(mixed $value, ?string $key): mixed
    {
        if (is_array($value)) {
            $clean = [];

            foreach ($value as $childKey => $childValue) {
                $resolvedKey = is_string($childKey) ? $childKey : $key;
                $clean[$childKey] = self::sanitizeValue($childValue, $resolvedKey);
            }

            return $clean;
        }

        if (! is_string($value)) {
            return $value;
        }

        if (self::shouldSkipKey($key)) {
            return $value;
        }

        if (self::isRawJsonKey($key)) {
            return str_replace("\0", '', $value);
        }

        $multiline = self::isMultilineKey($key);
        $cleaned = self::plainText($value, $multiline);

        return mb_substr($cleaned, 0, self::maxLengthForKey($key));
    }

    private static function shouldSkipKey(?string $key): bool
    {
        if ($key === null || $key === '') {
            return false;
        }

        $normalized = strtolower($key);

        if (in_array($normalized, self::SKIP_KEYS, true)) {
            return true;
        }

        return str_contains($normalized, 'password');
    }

    private static function isMultilineKey(?string $key): bool
    {
        if ($key === null || $key === '') {
            return false;
        }

        return in_array(strtolower($key), self::MULTILINE_KEYS, true);
    }

    private static function isRawJsonKey(?string $key): bool
    {
        if ($key === null || $key === '') {
            return false;
        }

        $normalized = strtolower($key);

        foreach (self::RAW_JSON_KEY_FRAGMENTS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private static function maxLengthForKey(?string $key): int
    {
        $normalized = strtolower((string) $key);

        return match (true) {
            $normalized === 'search', $normalized === 'q', $normalized === 'query' => self::SEARCH_MAX,
            $normalized === 'message' => self::AI_PROMPT_MAX,
            $normalized === 'title', $normalized === 'location', $normalized === 'venue' => self::TITLE_MAX,
            $normalized === 'description', $normalized === 'note', $normalized === 'notes' => self::DESCRIPTION_MAX,
            default => self::DEFAULT_STRING_MAX,
        };
    }
}
