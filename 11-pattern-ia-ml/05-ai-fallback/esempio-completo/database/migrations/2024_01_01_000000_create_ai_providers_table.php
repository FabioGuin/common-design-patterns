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
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('api_key')->nullable();
            $table->string('base_url')->nullable();
            $table->integer('priority')->default(999);
            $table->integer('timeout')->default(30);
            $table->integer('retry_attempts')->default(3);
            $table->boolean('enabled')->default(true);
            $table->enum('health_status', ['healthy', 'unhealthy', 'warning', 'unknown'])->default('unknown');
            $table->timestamp('last_health_check')->nullable();
            $table->integer('failure_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->float('average_response_time')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'priority']);
            $table->index(['health_status', 'enabled']);
            $table->index(['failure_count', 'success_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
