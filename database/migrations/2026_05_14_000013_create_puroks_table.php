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
        Schema::create('puroks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['barangay_id', 'name']);
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'purok_id')) {
                $table->foreignId('purok_id')->nullable()->after('barangay')->constrained('puroks')->nullOnDelete();
            }
        });

        Schema::table('resident_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('resident_requests', 'purok_id')) {
                $table->foreignId('purok_id')->nullable()->after('barangay')->constrained('puroks')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resident_requests', function (Blueprint $table) {
            if (Schema::hasColumn('resident_requests', 'purok_id')) {
                $table->dropConstrainedForeignId('purok_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'purok_id')) {
                $table->dropConstrainedForeignId('purok_id');
            }
        });

        Schema::dropIfExists('puroks');
    }
};
