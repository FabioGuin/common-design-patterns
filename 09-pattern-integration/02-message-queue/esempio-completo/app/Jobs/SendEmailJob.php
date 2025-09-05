<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    private string $to;
    private string $subject;
    private string $body;
    private string $type;

    /**
     * Create a new job instance.
     */
    public function __construct(string $to, string $subject, string $body, string $type = 'text')
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing email job', [
            'to' => $this->to,
            'subject' => $this->subject,
            'type' => $this->type
        ]);

        try {
            // Simulate email sending
            $this->simulateEmailSending();
            
            Log::info('Email sent successfully', [
                'to' => $this->to,
                'subject' => $this->subject
            ]);

        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'to' => $this->to,
                'subject' => $this->subject,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Simulate email sending
     */
    private function simulateEmailSending(): void
    {
        // Simulate network delay
        usleep(500000); // 500ms
        
        // Simulate occasional failures
        if (rand(1, 10) === 1) {
            throw new \Exception('Email service temporarily unavailable');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Email job failed permanently', [
            'to' => $this->to,
            'subject' => $this->subject,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
