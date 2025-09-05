<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetryAttempt extends Model
{
    protected $table = 'retry_attempts';
    
    protected $fillable = [
        'service_name',
        'attempt_number',
        'error_code',
        'error_message',
        'execution_time',
        'memory_used',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'error_code' => 'integer',
        'execution_time' => 'float',
        'memory_used' => 'integer',
    ];

    public $timestamps = true;
}
