<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('household_monitoring_visits', 'bmi_value')) {
            return;
        }

        DB::statement('ALTER TABLE household_monitoring_visits MODIFY bmi_value DECIMAL(5,1) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('household_monitoring_visits', 'bmi_value')) {
            return;
        }

        DB::statement('ALTER TABLE household_monitoring_visits MODIFY bmi_value DECIMAL(4,1) NULL');
    }
};
