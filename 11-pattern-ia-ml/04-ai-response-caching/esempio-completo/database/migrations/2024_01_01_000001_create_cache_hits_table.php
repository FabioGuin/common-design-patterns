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
        Schema::create('cache_hits', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key')->index();
            $table->string('strategy')->index();
            $table->enum('type', ['hit', 'miss'])->index();
            $table->float('response_time', 8, 3)->default(0);
            $table->float('hit_rate', 5, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['strategy', 'type']);
            $table->index(['type', 'created_at']);
            $table->index(['response_time', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_hits');
    }
};
