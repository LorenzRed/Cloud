<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inbox_messages', function (Blueprint $table) {
            if (Schema::hasColumn('inbox_messages', 'resident_request_id')) {
                $table->dropConstrainedForeignId('resident_request_id');
            }

            if (Schema::hasColumn('inbox_messages', 'sender_id')) {
                $table->dropConstrainedForeignId('sender_id');
            }

            $columns = collect([
                'attachment_path',
                'edited_at',
                'unsent_at',
                'hidden_for_sender_at',
            ])->filter(fn (string $column) => Schema::hasColumn('inbox_messages', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('inbox_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('inbox_messages', 'resident_request_id')) {
                $table->foreignId('resident_request_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('inbox_messages', 'sender_id')) {
                $table->foreignId('sender_id')->nullable()->after('resident_request_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('inbox_messages', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('message');
            }

            if (! Schema::hasColumn('inbox_messages', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('read_at');
            }

            if (! Schema::hasColumn('inbox_messages', 'unsent_at')) {
                $table->timestamp('unsent_at')->nullable()->after('edited_at');
            }

            if (! Schema::hasColumn('inbox_messages', 'hidden_for_sender_at')) {
                $table->timestamp('hidden_for_sender_at')->nullable()->after('unsent_at');
            }
        });
    }
};
