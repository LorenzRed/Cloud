<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('household_monitoring_visits', 'bmi_value')) {
            return;
        }

        Schema::table('household_monitoring_visits', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->decimal('bmi_value', 5, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('household_monitoring_visits', 'bmi_value')) {
            return;
        }

        Schema::table('household_monitoring_visits', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->decimal('bmi_value', 4, 1)->nullable()->change();
        });
    }
};
