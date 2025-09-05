<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulkhead_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('metric_type');
            $table->float('execution_time');
            $table->bigInteger('memory_used');
            $table->integer('active_threads')->default(0);
            $table->integer('active_connections')->default(0);
            $table->integer('queue_length')->default(0);
            $table->integer('total_executions')->default(0);
            $table->integer('successful_executions')->default(0);
            $table->integer('failed_executions')->default(0);
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('service_name');
            $table->index('metric_type');
            $table->index('created_at');
            $table->index(['service_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulkhead_metrics');
    }
};
