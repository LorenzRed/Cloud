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
        Schema::create('resident_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_request_id')->constrained('resident_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('age');
            $table->date('transaction_date');
            $table->char('gender', 1);
            $table->string('philhealth_number', 50);
            $table->unsignedSmallInteger('amlodipine_pcs')->nullable();
            $table->unsignedSmallInteger('losartan_pcs')->nullable();
            $table->unsignedSmallInteger('metoprolol_pcs')->nullable();
            $table->unsignedSmallInteger('simvastatin_pcs')->nullable();
            $table->unsignedSmallInteger('metformin_pcs')->nullable();
            $table->unsignedSmallInteger('gliclazide_pcs')->nullable();
            $table->timestamps();

            $table->index(['resident_request_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resident_transactions');
    }
};
