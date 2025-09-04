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
        Schema::create('circuit_breaker_states', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->enum('state', ['closed', 'open', 'half_open'])->index();
            $table->integer('failure_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->timestamp('last_failure_time')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('half_opened_at')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['state', 'opened_at']);
            $table->index(['failure_count', 'success_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circuit_breaker_states');
    }
};
