<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saga_events', function (Blueprint $table) {
            $table->id();
            $table->string('saga_id')->index();
            $table->string('event_type');
            $table->text('description');
            $table->enum('status', ['success', 'error', 'pending'])->default('pending');
            $table->json('data')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saga_events');
    }
};
