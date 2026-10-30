<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['resident_requests', 'users'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'years_of_residency')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->unsignedSmallInteger('years_of_residency')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['resident_requests', 'users'] as $tableName) {
            if (Schema::hasColumn($tableName, 'years_of_residency')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropColumn('years_of_residency');
                });
            }
        }
    }
};
