<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('resident_requests', 'family_number')) {
                $table->string('family_number')->nullable()->after('address');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'family_number')) {
                $table->string('family_number')->nullable()->after('household_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            if (Schema::hasColumn('resident_requests', 'family_number')) {
                $table->dropColumn('family_number');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'family_number')) {
                $table->dropColumn('family_number');
            }
        });
    }
};
