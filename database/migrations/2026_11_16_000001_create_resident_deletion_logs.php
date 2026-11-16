<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->softDeletes());
        Schema::table('resident_requests', fn (Blueprint $table) => $table->softDeletes());
        Schema::create('resident_deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resident_request_id')->index();
            $table->string('resident_name');
            $table->string('member_name');
            $table->string('action');
            $table->unsignedBigInteger('deleted_by');
            $table->string('deleted_by_name');
            $table->text('changes');
            $table->timestamp('deleted_at');
            $table->unsignedBigInteger('restored_by')->nullable();
            $table->string('restored_by_name')->nullable();
            $table->timestamp('restored_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_deletion_logs');
        Schema::table('resident_requests', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('users', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
