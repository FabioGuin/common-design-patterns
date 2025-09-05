<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('throttling_events', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('identifier');
            $table->string('endpoint')->nullable();
            $table->string('event_type'); // throttled, error
            $table->integer('rate_limit');
            $table->integer('window_seconds');
            $table->text('error_message');
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('service_name');
            $table->index('identifier');
            $table->index('event_type');
            $table->index('created_at');
            $table->index(['service_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('throttling_events');
    }
};
