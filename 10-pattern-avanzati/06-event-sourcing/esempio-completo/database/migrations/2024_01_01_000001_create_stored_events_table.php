<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_events', function (Blueprint $table) {
            $table->id();
            $table->string('aggregate_id');
            $table->string('event_type');
            $table->json('event_data');
            $table->integer('version');
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('aggregate_id');
            $table->index('event_type');
            $table->index(['aggregate_id', 'version']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_events');
    }
};
