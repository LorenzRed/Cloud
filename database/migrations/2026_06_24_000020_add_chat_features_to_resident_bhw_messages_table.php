<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_bhw_messages', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('message');
            $table->timestamp('edited_at')->nullable()->after('read_at');
            $table->timestamp('unsent_at')->nullable()->after('edited_at');
            $table->timestamp('hidden_for_sender_at')->nullable()->after('unsent_at');
        });
    }

    public function down(): void
    {
        Schema::table('resident_bhw_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'edited_at', 'unsent_at', 'hidden_for_sender_at']);
        });
    }
};
