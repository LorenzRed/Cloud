<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('actor_name');
            $table->string('actor_role')->nullable();
            $table->string('action');
            $table->string('route_name')->nullable();
            $table->string('method', 10);
            $table->json('targets')->nullable();
            $table->unsignedSmallInteger('status_code');
            $table->string('outcome', 20)->index();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_activity_logs');
    }
};
