<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('resident_requests', 'parent_role')) {
                $table->string('parent_role')->nullable()->after('full_name');
            }
            if (! Schema::hasColumn('resident_requests', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable()->after('age');
            }
            if (! Schema::hasColumn('resident_requests', 'civil_status')) {
                $table->string('civil_status')->nullable()->after('place_of_birth');
            }
            if (! Schema::hasColumn('resident_requests', 'household_children')) {
                $table->json('household_children')->nullable()->after('civil_status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'parent_role')) {
                $table->string('parent_role')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable()->after('civil_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            $table->dropColumn(['parent_role', 'place_of_birth', 'civil_status', 'household_children']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['parent_role', 'place_of_birth']);
        });
    }
};
