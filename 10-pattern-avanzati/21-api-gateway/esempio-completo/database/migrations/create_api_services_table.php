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
        Schema::create('api_services', function (Blueprint $table) {
            $table->id();
            $table->string('id')->unique();
            $table->string('name');
            $table->string('version');
            $table->string('base_url');
            $table->string('health_endpoint');
            $table->string('status')->default('unknown');
            $table->timestamp('last_check')->nullable();
            $table->timestamp('registered_at');
            $table->json('config')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'last_check']);
            $table->index('registered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_services');
    }
};
