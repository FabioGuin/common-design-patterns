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
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('provider');
            $table->text('description')->nullable();
            $table->json('capabilities')->nullable();
            $table->decimal('cost_per_token', 10, 8)->default(0);
            $table->integer('max_tokens')->default(4096);
            $table->integer('context_window')->default(4096);
            $table->integer('priority')->default(5);
            $table->boolean('enabled')->default(true);
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['provider', 'enabled']);
            $table->index(['priority', 'enabled']);
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
