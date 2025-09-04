<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->onDelete('set null');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('metadata')->nullable();
            $table->json('settings')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->integer('version')->default(1);
            $table->foreignId('parent_id')->nullable()->constrained('documents')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
