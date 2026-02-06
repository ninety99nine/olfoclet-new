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
        Schema::create('business_kpi_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_kpi_id')->constrained('business_kpis')->cascadeOnDelete();
            $table->foreignUuid('ussd_session_id')->nullable()->constrained('ussd_sessions')->nullOnDelete();
            $table->decimal('metric_value', 18, 4)->nullable();
            $table->timestamps();

            $table->index(['business_kpi_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_kpi_records');
    }
};
