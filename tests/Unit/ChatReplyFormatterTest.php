<?php

namespace Tests\Unit;

use App\Support\ChatReplyFormatter;
use PHPUnit\Framework\TestCase;

class ChatReplyFormatterTest extends TestCase
{
    public function test_it_removes_formatting_without_losing_the_reply(): void
    {
        $input = "### Next steps\n\n**Contact your BHW**\n- Bring your _records_.\n//////\n\"\"\"\n****\n```text\nKeep this advice.\n```";

        $this->assertSame(
            "Next steps\n\nContact your BHW\n• Bring your records.\n\nKeep this advice.",
            ChatReplyFormatter::plainText($input)
        );
    }

    public function test_it_preserves_links_numbers_and_meaningful_punctuation(): void
    {
        $input = "1. Visit [the clinic](https://example.com/clinic).\n2. Hours: 8:00–12:00.\nUse your patient_id. Say \"hello\". Call #123.";

        $this->assertSame(
            "1. Visit the clinic (https://example.com/clinic).\n2. Hours: 8:00–12:00.\nUse your patient_id. Say \"hello\". Call #123.",
            ChatReplyFormatter::plainText($input)
        );
    }
}
