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
        Schema::create('ussd_accounts', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('msisdn', 20);
            $table->string('country', 2)->nullable();
            $table->string('network', 40)->nullable();
            $table->boolean('simulated');

            $table->unsignedInteger('total_sessions')->default(0);
            $table->unsignedInteger('total_failed_sessions')->default(0);
            $table->unsignedInteger('total_successful_sessions')->default(0);
            $table->unsignedInteger('total_mobile_sessions')->default(0);
            $table->unsignedInteger('total_simulator_sessions')->default(0);

            $table->unsignedTinyInteger('total_steps')->default(0);
            $table->unsignedInteger('total_duration_ms')->default(0);

            $table->timestamp('blocked_at')->nullable();
            $table->unsignedTinyInteger('open_flags_count')->default(0);

            $table->foreignUuid('app_id')->constrained('apps')->cascadeOnDelete();

            $table->timestamps();

            $table->index('msisdn');
            $table->index('country');
            $table->index('network');
            $table->index('blocked_at');
            $table->index('open_flags_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ussd_accounts');
    }
};
