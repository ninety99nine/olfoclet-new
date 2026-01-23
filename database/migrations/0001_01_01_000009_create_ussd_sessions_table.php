<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ussd_sessions', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->string('msisdn', 20);
            $table->string('shortcode', 16);
            $table->string('country', 2)->nullable();
            $table->string('network', 40)->nullable();
            $table->string('session_id', 64)->nullable();

            $table->boolean('successful');
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('total_steps')->default(0);
            $table->unsignedTinyInteger('open_flags_count')->default(0);
            $table->unsignedInteger('total_duration_ms')->default(0);

            $table->boolean('simulated');
            $table->text('last_step_content')->nullable();

            $table->foreignUuid('app_id')->constrained('apps')->cascadeOnDelete();
            $table->foreignUuid('ussd_account_id')->constrained('ussd_accounts')->cascadeOnDelete();

            $table->timestamps();

            $table->index('msisdn');
            $table->index('country');
            $table->index('network');
            $table->index('shortcode');
            $table->index('open_flags_count');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ussd_sessions');
    }
};
