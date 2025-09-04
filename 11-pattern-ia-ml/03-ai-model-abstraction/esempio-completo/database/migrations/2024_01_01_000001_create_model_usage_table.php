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
        Schema::create('model_usage', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->string('model_name');
            $table->string('provider');
            $table->text('prompt');
            $table->json('response')->nullable();
            $table->boolean('success')->default(false);
            $table->decimal('duration', 8, 3)->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->integer('tokens_used')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['model_name', 'created_at']);
            $table->index(['provider', 'created_at']);
            $table->index(['success', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_usage');
    }
};
