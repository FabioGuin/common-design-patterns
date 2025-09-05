<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->json('event_data');
            $table->unsignedBigInteger('aggregate_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'published', 'failed'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->timestamp('scheduled_at');
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index('aggregate_id');
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
    }
};
