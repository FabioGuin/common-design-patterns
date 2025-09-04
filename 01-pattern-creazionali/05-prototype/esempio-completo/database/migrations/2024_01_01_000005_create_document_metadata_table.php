<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->string('key');
            $table->json('value');
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['document_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_metadata');
    }
};
