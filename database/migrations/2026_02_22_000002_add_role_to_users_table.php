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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('users')->after('email');
            }

            if (! Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->nullable()->after('name');
            }

            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('gender');
            }

            if (! Schema::hasColumn('users', 'age')) {
                $table->unsignedTinyInteger('age')->nullable()->after('date_of_birth');
            }

            if (! Schema::hasColumn('users', 'civil_status')) {
                $table->string('civil_status')->nullable()->after('age');
            }

            if (! Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('civil_status');
            }

            if (! Schema::hasColumn('users', 'barangay')) {
                $table->string('barangay')->nullable()->after('contact_number');
            }

            if (! Schema::hasColumn('users', 'household_no')) {
                $table->string('household_no')->nullable()->after('barangay');
            }

            if (! Schema::hasColumn('users', 'municipality')) {
                $table->string('municipality')->nullable()->after('household_no');
            }

            if (! Schema::hasColumn('users', 'province')) {
                $table->string('province')->nullable()->after('municipality');
            }

            if (! Schema::hasColumn('users', 'profile_picture_path')) {
                $table->string('profile_picture_path')->nullable()->after('province');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'profile_picture_path',
                'province',
                'municipality',
                'household_no',
                'barangay',
                'contact_number',
                'civil_status',
                'age',
                'date_of_birth',
                'gender',
                'full_name',
                'role',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
