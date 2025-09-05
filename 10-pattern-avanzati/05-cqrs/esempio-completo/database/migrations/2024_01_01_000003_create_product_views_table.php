<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_read')->create('product_views', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->integer('stock');
            $table->string('category');
            $table->json('attributes')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('category');
            $table->index('price');
            $table->index('is_available');
            $table->index(['category', 'is_available']);
            $table->index(['price', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_read')->dropIfExists('product_views');
    }
};
