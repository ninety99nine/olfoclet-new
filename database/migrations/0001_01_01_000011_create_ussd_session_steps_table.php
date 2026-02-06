<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ussd_session_steps', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('step_id', 64)->nullable();
            $table->string('step_title', 120)->nullable();
            $table->longText('step_content')->nullable();
            $table->text('reply')->nullable();

            $table->boolean('paginated')->default(false);
            $table->unsignedTinyInteger('page_number')->default(0);
            $table->unsignedTinyInteger('total_pages')->default(0);
            $table->boolean('terminated_by_system')->default(false);
            $table->unsignedInteger('total_failed_validation')->default(0);
            $table->unsignedInteger('total_duration_ms')->default(0);

            $table->boolean('successful');
            $table->text('error_message')->nullable();

            $table->foreignUuid('ussd_session_id')->constrained('ussd_sessions')->cascadeOnDelete();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ussd_session_steps');
    }
};
