<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_id')->unique();
            $table->string('product_id');
            $table->integer('quantity');
            $table->string('order_id');
            $table->string('status');
            $table->timestamp('expires_at');
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('product_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
    }
};
