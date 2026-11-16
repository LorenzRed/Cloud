<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_errors', function (Blueprint $table) {
            $table->id();
            $table->string('category')->index();
            $table->string('exception_type')->nullable();
            $table->string('cause');
            $table->string('suggested_action');
            $table->string('route_name')->nullable();
            $table->string('method', 10)->nullable();
            $table->unsignedSmallInteger('status_code')->default(500);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('actor_name');
            $table->string('actor_role')->nullable();
            $table->string('source_file')->nullable();
            $table->unsignedInteger('source_line')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_errors');
    }
};
