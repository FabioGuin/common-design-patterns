<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id')->unique();
            $table->string('order_id');
            $table->string('type');
            $table->string('recipient');
            $table->string('status');
            $table->timestamp('sent_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('order_id');
            $table->index('type');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
