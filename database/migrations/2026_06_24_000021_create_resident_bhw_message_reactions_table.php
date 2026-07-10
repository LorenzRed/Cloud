<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resident_bhw_message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_bhw_message_id')
                ->constrained('resident_bhw_messages')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('emoji', 32);
            $table->timestamps();

            $table->unique(['resident_bhw_message_id', 'user_id'], 'rbhw_msg_reaction_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_bhw_message_reactions');
    }
};
