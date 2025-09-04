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
        Schema::create('prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('template');
            $table->json('variables');
            $table->json('validation_rules')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_custom')->default(false);
            $table->json('result')->nullable();
            $table->json('validation_result')->nullable();
            $table->boolean('success')->default(false);
            $table->float('quality_score')->default(0);
            $table->float('cost')->default(0);
            $table->float('duration')->default(0);
            $table->timestamps();
            
            // Indici per performance
            $table->index('name');
            $table->index('is_custom');
            $table->index('success');
            $table->index('quality_score');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_templates');
    }
};
