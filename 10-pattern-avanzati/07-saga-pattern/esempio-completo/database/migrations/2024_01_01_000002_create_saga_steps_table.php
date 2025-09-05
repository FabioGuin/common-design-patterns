<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saga_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saga_id')->constrained()->onDelete('cascade');
            $table->string('step_name');
            $table->string('status');
            $table->json('data')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('saga_id');
            $table->index('step_name');
            $table->index('status');
            $table->index('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saga_steps');
    }
};
