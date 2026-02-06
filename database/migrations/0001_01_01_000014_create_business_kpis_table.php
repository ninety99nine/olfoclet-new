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
        Schema::create('business_kpis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('app_id')->constrained('apps')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('metric_type'); // count, money, percentage, distribution
            $table->string('chart_type'); // line, bar, doughnut
            $table->boolean('break_down_by_network')->default(false);
            $table->boolean('break_down_by_country')->default(false);
            $table->string('breakdown_display')->default('time'); // time, distribution
            $table->string('currency', 10)->nullable();
            $table->json('distribution_options')->nullable();
            $table->string('x_axis_name')->nullable();
            $table->string('y_axis_name')->nullable();
            $table->timestamps();

            $table->index(['app_id', 'metric_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_kpis');
    }
};
