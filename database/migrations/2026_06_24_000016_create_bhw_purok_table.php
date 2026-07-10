<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bhw_purok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purok_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'purok_id']);
        });

        DB::table('users')
            ->where('role', 'BHW')
            ->whereNotNull('purok_id')
            ->orderBy('id')
            ->each(function ($user) {
                DB::table('bhw_purok')->insertOrIgnore([
                    'user_id' => $user->id,
                    'purok_id' => $user->purok_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('bhw_purok');
    }
};
