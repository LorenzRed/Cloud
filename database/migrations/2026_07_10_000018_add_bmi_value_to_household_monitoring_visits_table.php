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
        Schema::table('household_monitoring_visits', function (Blueprint $table) {
            $table->decimal('bmi_value', 5, 1)->nullable()->after('height_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('household_monitoring_visits', function (Blueprint $table) {
            $table->dropColumn('bmi_value');
        });
    }
};
