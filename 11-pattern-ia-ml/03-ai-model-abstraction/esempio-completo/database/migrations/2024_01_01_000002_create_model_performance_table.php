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
        Schema::create('model_performance', function (Blueprint $table) {
            $table->id();
            $table->string('model_name')->unique();
            $table->integer('total_requests')->default(0);
            $table->integer('successful_requests')->default(0);
            $table->decimal('total_duration', 12, 3)->default(0);
            $table->decimal('total_cost', 12, 6)->default(0);
            $table->integer('total_tokens')->default(0);
            $table->decimal('average_response_time', 8, 3)->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->decimal('average_cost', 10, 6)->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('model_name');
            $table->index('success_rate');
            $table->index('average_response_time');
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_performance');
    }
};
