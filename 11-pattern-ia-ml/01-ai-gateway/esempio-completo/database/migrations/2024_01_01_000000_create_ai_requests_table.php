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
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->string('provider');
            $table->text('prompt');
            $table->json('response')->nullable();
            $table->boolean('success')->default(false);
            $table->float('duration')->default(0);
            $table->float('cost')->default(0);
            $table->integer('tokens_used')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indici per performance
            $table->index('provider');
            $table->index('success');
            $table->index('created_at');
            $table->index(['provider', 'success']);
            $table->index(['created_at', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
