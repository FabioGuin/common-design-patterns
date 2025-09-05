<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sharding_events', function (Blueprint $table) {
            $table->id();
            $table->string('entity');
            $table->string('shard');
            $table->string('key');
            $table->string('event_type'); // error, success
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('entity');
            $table->index('shard');
            $table->index('event_type');
            $table->index('created_at');
            $table->index(['entity', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sharding_events');
    }
};
