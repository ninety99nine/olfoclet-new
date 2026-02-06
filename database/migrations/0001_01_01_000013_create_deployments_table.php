<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('country', 2);
            $table->string('network', 40);
            $table->boolean('active')->default(true);
            $table->boolean('individual_replies')->default(true);
            $table->unsignedTinyInteger('max_character_length')->default(182);

            $table->enum('incoming_format', ['xml', 'json']);
            $table->text('transform_request_language', ['php', 'python', 'javascript']);
            $table->text('transform_request_code');

            $table->enum('outgoing_format', ['xml', 'json', 'plain text']);
            $table->text('transform_response_language', ['php', 'python', 'javascript']);
            $table->text('transform_response_code');

            $table->foreignUuid('app_id')->constrained('apps')->cascadeOnDelete();
            $table->foreignUuid('version_id')->nullable()->references('id')->on('versions')->nullOnDelete();
            $table->timestamps();

            $table->index('country');
            $table->index('network');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
