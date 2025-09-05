<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sharding_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('entity');
            $table->string('shard');
            $table->string('key');
            $table->float('execution_time')->default(0);
            $table->bigInteger('memory_used')->default(0);
            $table->integer('total_queries')->default(0);
            $table->integer('successful_queries')->default(0);
            $table->integer('failed_queries')->default(0);
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('entity');
            $table->index('shard');
            $table->index('created_at');
            $table->index(['entity', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sharding_metrics');
    }
};
