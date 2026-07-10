<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('resident_requests', 'spouse_full_name')) {
                $table->string('spouse_full_name')->nullable()->after('educational_attainment');
            }
            if (! Schema::hasColumn('resident_requests', 'spouse_details')) {
                $table->json('spouse_details')->nullable()->after('spouse_full_name');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'spouse_full_name')) {
                $table->string('spouse_full_name')->nullable()->after('educational_attainment');
            }
            if (! Schema::hasColumn('users', 'spouse_details')) {
                $table->json('spouse_details')->nullable()->after('spouse_full_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            $table->dropColumn(['spouse_full_name', 'spouse_details']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['spouse_full_name', 'spouse_details']);
        });
    }
};
