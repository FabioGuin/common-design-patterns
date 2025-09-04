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
        Schema::create('cache_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name')->index();
            $table->string('strategy')->nullable()->index();
            $table->string('period')->index();
            $table->float('value', 10, 3);
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            $table->index(['metric_name', 'period', 'recorded_at']);
            $table->index(['strategy', 'metric_name', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_analytics');
    }
};
