<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retry_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->integer('attempt_number');
            $table->integer('error_code');
            $table->text('error_message');
            $table->float('execution_time');
            $table->bigInteger('memory_used');
            $table->timestamps();
            
            // Indici per ottimizzare le query
            $table->index('service_name');
            $table->index('attempt_number');
            $table->index('error_code');
            $table->index('created_at');
            $table->index(['service_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retry_attempts');
    }
};
