<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prompt_tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_id')->unique();
            $table->string('template_name');
            $table->json('variables');
            $table->json('results');
            $table->json('analysis');
            $table->float('success_rate')->default(0);
            $table->float('average_quality')->default(0);
            $table->float('total_cost')->default(0);
            $table->float('average_duration')->default(0);
            $table->integer('iterations')->default(0);
            $table->timestamps();
            
            // Indici per performance
            $table->index('test_id');
            $table->index('template_name');
            $table->index('success_rate');
            $table->index('average_quality');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_tests');
    }
};
