<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name')->nullable();
            $table->string('email')->nullable();
            $table->foreignUuid('user_id')->nullable();
            $table->foreignUuid('app_id');
            $table->foreignUuid('role_id');
            $table->boolean('creator')->default(false);
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('app_id')->references('id')->on('apps')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user');
    }
};
