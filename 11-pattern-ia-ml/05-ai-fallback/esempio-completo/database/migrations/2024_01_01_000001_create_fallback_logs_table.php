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
        Schema::create('fallback_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->index();
            $table->enum('status', ['success', 'error', 'fallback_success', 'static_fallback'])->index();
            $table->string('provider')->index();
            $table->string('strategy')->index();
            $table->float('response_time', 8, 3)->default(0);
            $table->json('context')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->boolean('fallback_used')->default(false);
            $table->enum('circuit_breaker_state', ['closed', 'open', 'half_open'])->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['provider', 'status']);
            $table->index(['strategy', 'status']);
            $table->index(['retry_count', 'created_at']);
            $table->index(['fallback_used', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fallback_logs');
    }
};
