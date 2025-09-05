<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeout_events', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->integer('timeout_ms');
            $table->float('execution_time');
            $table->string('event_type'); // timeout, error
            $table->text('error_message');
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('service_name');
            $table->index('event_type');
            $table->index('created_at');
            $table->index(['service_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeout_events');
    }
};
