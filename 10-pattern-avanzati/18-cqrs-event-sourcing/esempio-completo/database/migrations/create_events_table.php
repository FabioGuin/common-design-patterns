<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->string('aggregate_id');
            $table->json('data');
            $table->json('metadata')->nullable();
            $table->integer('version')->default(1);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            
            $table->index(['aggregate_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
