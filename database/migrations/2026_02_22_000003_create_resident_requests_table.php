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
        if (! Schema::hasTable('resident_requests')) {
            Schema::create('resident_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('full_name');
                $table->string('barangay');
                $table->string('contact')->nullable();
                $table->string('address')->nullable();
                $table->unsignedTinyInteger('age')->nullable();
                $table->string('status')->default('pending');
                $table->string('id_type')->nullable();
                $table->string('id_other_label')->nullable();
                $table->string('id_front_path')->nullable();
                $table->string('id_back_path')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->unique('user_id');
                $table->index('status');
            });
        }

        Schema::table('resident_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('resident_requests', 'id_type')) {
                $table->string('id_type')->nullable()->after('status');
            }

            if (! Schema::hasColumn('resident_requests', 'id_other_label')) {
                $table->string('id_other_label')->nullable()->after('id_type');
            }

            if (! Schema::hasColumn('resident_requests', 'id_front_path')) {
                $table->string('id_front_path')->nullable()->after('id_other_label');
            }

            if (! Schema::hasColumn('resident_requests', 'id_back_path')) {
                $table->string('id_back_path')->nullable()->after('id_front_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resident_requests');
    }
};
