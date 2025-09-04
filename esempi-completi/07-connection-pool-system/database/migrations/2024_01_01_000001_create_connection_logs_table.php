<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connection_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pool_name');
            $table->string('action'); // acquire, release, create, destroy
            $table->string('resource_type');
            $table->string('acquired_by')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->boolean('success');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['pool_name', 'action']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connection_logs');
    }
};
