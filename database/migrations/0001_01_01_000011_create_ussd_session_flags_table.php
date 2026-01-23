<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ussd_session_flags', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->string('category', 50); // bug, ux, performance, security, content, fraud, other
            $table->string('priority', 20); // low, medium, high, critical
            $table->text('comment');

            $table->enum('status', ['open', 'resolved'])->default('open');  // open, resolved
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_comment')->nullable();

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignUuid('app_id')->constrained('apps')->cascadeOnDelete();
            $table->foreignUuid('ussd_session_id')->constrained('ussd_sessions')->cascadeOnDelete();
            $table->foreignUuid('ussd_session_step_id')->nullable()->constrained('ussd_session_steps')->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('category');
            $table->index('priority');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ussd_session_flags');
    }
};
