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
        Schema::create('api_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->string('method');
            $table->string('path');
            $table->text('url');
            $table->string('ip');
            $table->text('user_agent');
            $table->json('headers');
            $table->json('query');
            $table->json('body');
            $table->string('user_id')->nullable();
            $table->integer('status_code');
            $table->decimal('response_time', 8, 3);
            $table->boolean('success');
            $table->boolean('cached')->default(false);
            $table->string('service');
            $table->string('gateway');
            $table->timestamps();
            
            $table->index(['method', 'path']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status_code', 'created_at']);
            $table->index(['success', 'created_at']);
            $table->index(['service', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_requests');
    }
};
