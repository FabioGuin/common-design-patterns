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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('author');
            $table->timestamp('published_at')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
            
            // Indici per performance
            $table->index('published_at');
            $table->index('category');
            $table->index('author');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
