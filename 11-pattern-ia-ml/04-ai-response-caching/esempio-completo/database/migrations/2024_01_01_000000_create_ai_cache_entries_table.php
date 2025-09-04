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
        Schema::create('ai_cache_entries', function (Blueprint $table) {
            $table->id();
            $table->string('original_key')->index();
            $table->string('cache_key')->unique();
            $table->string('strategy')->index();
            $table->integer('ttl');
            $table->timestamp('expires_at')->index();
            $table->bigInteger('size')->default(0);
            $table->boolean('compressed')->default(false);
            $table->json('tags')->nullable();
            $table->integer('hit_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['strategy', 'expires_at']);
            $table->index(['hit_count', 'last_accessed_at']);
            $table->index(['compressed', 'size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_cache_entries');
    }
};
