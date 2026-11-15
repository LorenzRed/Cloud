<?php

namespace App\Support;

final class ChatReplyFormatter
{
    public static function plainText(string $reply): string
    {
        $reply = str_replace(["\r\n", "\r"], "\n", $reply);
        // Remove formatting wrappers while keeping their actual content.
        $reply = preg_replace('/^\h*(?:`{3,}|~{3,})[^\n]*$/m', '', $reply);
        $reply = preg_replace('/^\h{0,3}#{1,6}\h+(.+?)(?:\h+#+)?\h*$/m', '$1', $reply);
        $reply = preg_replace('/^\h*>\h?/m', '', $reply);
        $reply = preg_replace('/!\[([^\]]*)\]\(([^\s)]+)\)/', '$1 ($2)', $reply);
        $reply = preg_replace('/\[([^\]]+)\]\(([^\s)]+)\)/', '$1 ($2)', $reply);
        $reply = preg_replace('/(\*\*|__|~~)(?=\S)(.+?)(?<=\S)\1/s', '$2', $reply);
        $reply = preg_replace('/(?<!\w)(\*|_)(?=\S)(.+?)(?<=\S)\1(?!\w)/', '$2', $reply);
        $reply = preg_replace('/`([^`\n]+)`/', '$1', $reply);
        $reply = preg_replace('/^\h*[-*+]\h+/m', '• ', $reply);
        // Decoration-only lines add noise, but punctuation in sentences and URLs stays intact.
        $reply = preg_replace('/^\h*[#*_=~\/\\\\"\x27`-]{2,}\h*$/m', '', $reply);
        $reply = preg_replace('/\n\h*\n(?:\h*\n)+/', "\n\n", $reply);

        return trim($reply);
    }
}
