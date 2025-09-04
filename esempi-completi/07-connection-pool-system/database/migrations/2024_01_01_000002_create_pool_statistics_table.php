<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pool_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('pool_name');
            $table->string('resource_type');
            $table->integer('max_size');
            $table->integer('current_size');
            $table->integer('available');
            $table->integer('in_use');
            $table->integer('total_created');
            $table->integer('total_acquired');
            $table->integer('total_released');
            $table->integer('total_failed');
            $table->float('utilization_percentage');
            $table->float('success_rate');
            $table->integer('healthy_resources');
            $table->integer('unhealthy_resources');
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['pool_name', 'recorded_at']);
            $table->index(['recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_statistics');
    }
};
