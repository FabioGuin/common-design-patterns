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
        Schema::create('api_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->json('permissions')->nullable();
            $table->string('api_key')->unique()->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_login')->nullable();
            $table->integer('rate_limit')->default(100);
            $table->integer('rate_window')->default(60);
            $table->timestamps();
            
            $table->index(['email', 'status']);
            $table->index(['role', 'status']);
            $table->index(['status', 'last_login']);
            $table->index('api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_users');
    }
};
