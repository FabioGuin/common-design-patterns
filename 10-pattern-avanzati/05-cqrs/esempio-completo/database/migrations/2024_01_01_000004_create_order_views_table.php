<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_read')->create('order_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('items');
            $table->decimal('total_amount', 10, 2);
            $table->text('shipping_address');
            $table->text('billing_address');
            $table->string('status')->default('pending');
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('user_id');
            $table->index('status');
            $table->index('total_amount');
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_read')->dropIfExists('order_views');
    }
};
