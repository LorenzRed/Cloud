<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('resident_requests', 'educational_attainment')) {
                $table->string('educational_attainment')->nullable()->after('civil_status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'educational_attainment')) {
                $table->string('educational_attainment')->nullable()->after('civil_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            $table->dropColumn('educational_attainment');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('educational_attainment');
        });
    }
};
