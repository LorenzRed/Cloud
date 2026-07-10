<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bns_child_weight_logs', function (Blueprint $table) {
            $table->id();
            $table->string('child_key', 64);
            $table->foreignId('resident_request_id')->constrained('resident_requests')->cascadeOnDelete();
            $table->unsignedSmallInteger('child_index');
            $table->decimal('weight_kg', 5, 2);
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->unsignedSmallInteger('age_in_months')->nullable();
            $table->date('recorded_on');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['child_key', 'recorded_on']);
        });

        Schema::create('bns_child_deworming_logs', function (Blueprint $table) {
            $table->id();
            $table->string('child_key', 64);
            $table->foreignId('resident_request_id')->constrained('resident_requests')->cascadeOnDelete();
            $table->unsignedSmallInteger('child_index');
            $table->date('given_on');
            $table->string('medicine')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['child_key', 'given_on']);
        });

        Schema::create('bns_child_vitamin_a_logs', function (Blueprint $table) {
            $table->id();
            $table->string('child_key', 64);
            $table->foreignId('resident_request_id')->constrained('resident_requests')->cascadeOnDelete();
            $table->unsignedSmallInteger('child_index');
            $table->date('given_on');
            $table->unsignedSmallInteger('age_in_months')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['child_key', 'given_on']);
        });

        Schema::create('bns_child_immunizations', function (Blueprint $table) {
            $table->id();
            $table->string('child_key', 64);
            $table->foreignId('resident_request_id')->constrained('resident_requests')->cascadeOnDelete();
            $table->unsignedSmallInteger('child_index');
            $table->string('vaccine_code', 64);
            $table->string('vaccine_name');
            $table->date('due_date');
            $table->date('given_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['child_key', 'vaccine_code']);
            $table->index(['child_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bns_child_immunizations');
        Schema::dropIfExists('bns_child_vitamin_a_logs');
        Schema::dropIfExists('bns_child_deworming_logs');
        Schema::dropIfExists('bns_child_weight_logs');
    }
};
