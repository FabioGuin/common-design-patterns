<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache_events', function (Blueprint $table) {
            $table->id();
            $table->string('entity');
            $table->string('key');
            $table->string('event_type'); // error, invalidation
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('entity');
            $table->index('event_type');
            $table->index('created_at');
            $table->index(['entity', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_events');
    }
};
