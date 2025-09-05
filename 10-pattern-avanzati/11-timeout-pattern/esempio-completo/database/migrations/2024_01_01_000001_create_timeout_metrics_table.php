<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeout_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->integer('timeout_ms');
            $table->float('execution_time');
            $table->bigInteger('memory_used');
            $table->integer('total_operations')->default(0);
            $table->integer('successful_operations')->default(0);
            $table->integer('timeout_operations')->default(0);
            $table->integer('error_operations')->default(0);
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('service_name');
            $table->index('created_at');
            $table->index(['service_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeout_metrics');
    }
};
