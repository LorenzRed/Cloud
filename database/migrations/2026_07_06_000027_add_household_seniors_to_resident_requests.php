<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('resident_requests', 'household_seniors')) {
                $table->json('household_seniors')->nullable()->after('household_children');
            }
        });
    }

    public function down(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            if (Schema::hasColumn('resident_requests', 'household_seniors')) {
                $table->dropColumn('household_seniors');
            }
        });
    }
};
