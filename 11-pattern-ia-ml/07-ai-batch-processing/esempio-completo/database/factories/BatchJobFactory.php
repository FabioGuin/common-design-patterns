<?php

namespace Database\Factories;

use App\Models\BatchJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchJobFactory extends Factory
{
    protected $model = BatchJob::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'status' => $this->faker->randomElement([
                BatchJob::STATUS_PENDING,
                BatchJob::STATUS_PROCESSING,
                BatchJob::STATUS_COMPLETED,
                BatchJob::STATUS_FAILED,
            ]),
            'total_requests' => $this->faker->numberBetween(1, 100),
            'processed_requests' => $this->faker->numberBetween(0, 50),
            'failed_requests' => $this->faker->numberBetween(0, 10),
            'provider' => $this->faker->randomElement(['openai', 'claude', 'gemini']),
            'model' => $this->faker->randomElement([
                'gpt-3.5-turbo',
                'gpt-4',
                'claude-3-sonnet',
                'gemini-pro',
            ]),
            'batch_size' => $this->faker->numberBetween(10, 100),
            'priority' => $this->faker->randomElement([
                BatchJob::PRIORITY_LOW,
                BatchJob::PRIORITY_NORMAL,
                BatchJob::PRIORITY_HIGH,
                BatchJob::PRIORITY_URGENT,
            ]),
            'scheduled_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'completed_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 week', 'now'),
            'metadata' => [
                'created_by' => $this->faker->name(),
                'source' => $this->faker->randomElement(['api', 'web', 'cli']),
                'tags' => $this->faker->words(3),
            ],
            'error_message' => $this->faker->optional(0.2)->sentence(),
            'processing_time_seconds' => $this->faker->optional(0.8)->randomFloat(2, 1, 300),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchJob::STATUS_PENDING,
            'processed_requests' => 0,
            'failed_requests' => 0,
            'completed_at' => null,
            'error_message' => null,
            'processing_time_seconds' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchJob::STATUS_PROCESSING,
            'processed_requests' => $this->faker->numberBetween(1, $attributes['total_requests'] - 1),
            'completed_at' => null,
            'error_message' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchJob::STATUS_COMPLETED,
            'processed_requests' => $attributes['total_requests'],
            'failed_requests' => 0,
            'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'error_message' => null,
            'processing_time_seconds' => $this->faker->randomFloat(2, 1, 300),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchJob::STATUS_FAILED,
            'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'error_message' => $this->faker->sentence(),
            'processing_time_seconds' => $this->faker->randomFloat(2, 1, 60),
        ]);
    }

    public function withProvider(string $provider): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => $provider,
        ]);
    }

    public function withPriority(string $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority,
        ]);
    }
}
