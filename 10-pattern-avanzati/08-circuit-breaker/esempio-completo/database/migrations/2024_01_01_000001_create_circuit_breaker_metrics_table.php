<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('circuit_breaker_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('metric_type');
            $table->string('state');
            $table->integer('failure_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('total_calls')->default(0);
            $table->integer('total_failures')->default(0);
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('service_name');
            $table->index('metric_type');
            $table->index('state');
            $table->index('created_at');
            $table->index(['service_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circuit_breaker_metrics');
    }
};
