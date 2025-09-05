<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('throttling_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('identifier');
            $table->string('endpoint')->nullable();
            $table->integer('rate_limit');
            $table->integer('window_seconds');
            $table->float('execution_time')->default(0);
            $table->bigInteger('memory_used')->default(0);
            $table->integer('total_requests')->default(0);
            $table->integer('successful_requests')->default(0);
            $table->integer('throttled_requests')->default(0);
            $table->integer('error_requests')->default(0);
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('service_name');
            $table->index('identifier');
            $table->index('endpoint');
            $table->index('created_at');
            $table->index(['service_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('throttling_metrics');
    }
};
