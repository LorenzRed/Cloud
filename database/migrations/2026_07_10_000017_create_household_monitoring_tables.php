<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('household_monitoring_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_request_id')->constrained('resident_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->string('member_type');
            $table->string('member_name');
            $table->string('member_source')->nullable();
            $table->unsignedSmallInteger('member_index')->nullable();
            $table->text('issue_notes')->nullable();
            $table->date('first_period_date')->nullable();
            $table->date('last_period_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['resident_request_id', 'member_type'], 'hm_cases_resident_type_idx');
        });

        Schema::create('household_monitoring_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_monitoring_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->date('visit_date');
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('muac_cm', 4, 1)->nullable();
            $table->string('anti_tetanus')->nullable();
            $table->string('blood_pressure')->nullable();
            $table->text('maintenance_medicines')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['household_monitoring_case_id', 'visit_date'], 'hm_visits_case_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_monitoring_visits');
        Schema::dropIfExists('household_monitoring_cases');
    }
};
