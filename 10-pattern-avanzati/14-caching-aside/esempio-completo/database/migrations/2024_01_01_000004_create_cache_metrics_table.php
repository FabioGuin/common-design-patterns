<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('entity');
            $table->string('key');
            $table->string('operation'); // hit, miss, write, error
            $table->float('execution_time')->default(0);
            $table->bigInteger('memory_used')->default(0);
            $table->integer('total_operations')->default(0);
            $table->integer('cache_hits')->default(0);
            $table->integer('cache_misses')->default(0);
            $table->integer('cache_errors')->default(0);
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('entity');
            $table->index('operation');
            $table->index('created_at');
            $table->index(['entity', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_metrics');
    }
};
