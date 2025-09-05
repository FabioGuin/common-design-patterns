<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_projections', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('customer_id');
            $table->json('items');
            $table->decimal('total_amount', 10, 2);
            $table->text('shipping_address');
            $table->string('status');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->string('delivery_confirmation')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->integer('version');
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('customer_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_projections');
    }
};
