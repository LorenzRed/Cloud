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
        Schema::table('resident_transactions', function (Blueprint $table) {
            $table->string('member_name')->nullable()->after('philhealth_number');
            $table->string('member_source', 40)->nullable()->after('member_name');
            $table->unsignedSmallInteger('member_index')->nullable()->after('member_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resident_transactions', function (Blueprint $table) {
            $table->dropColumn(['member_name', 'member_source', 'member_index']);
        });
    }
};
