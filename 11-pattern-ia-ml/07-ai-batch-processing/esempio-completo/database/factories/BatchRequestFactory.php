<?php

namespace Database\Factories;

use App\Models\BatchRequest;
use App\Models\BatchJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchRequestFactory extends Factory
{
    protected $model = BatchRequest::class;

    public function definition(): array
    {
        return [
            'batch_job_id' => BatchJob::factory(),
            'input' => $this->faker->sentence(10),
            'expected_output' => $this->faker->optional(0.3)->sentence(5),
            'actual_output' => $this->faker->optional(0.7)->sentence(8),
            'status' => $this->faker->randomElement([
                BatchRequest::STATUS_PENDING,
                BatchRequest::STATUS_PROCESSING,
                BatchRequest::STATUS_COMPLETED,
                BatchRequest::STATUS_FAILED,
            ]),
            'priority' => $this->faker->randomElement([
                BatchRequest::PRIORITY_LOW,
                BatchRequest::PRIORITY_NORMAL,
                BatchRequest::PRIORITY_HIGH,
                BatchRequest::PRIORITY_URGENT,
            ]),
            'error_message' => $this->faker->optional(0.2)->sentence(),
            'processing_time_ms' => $this->faker->optional(0.8)->numberBetween(100, 5000),
            'metadata' => [
                'source' => $this->faker->randomElement(['api', 'web', 'cli']),
                'user_id' => $this->faker->optional(0.7)->numberBetween(1, 1000),
                'tags' => $this->faker->words(2),
            ],
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchRequest::STATUS_PENDING,
            'actual_output' => null,
            'error_message' => null,
            'processing_time_ms' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchRequest::STATUS_PROCESSING,
            'actual_output' => null,
            'error_message' => null,
            'processing_time_ms' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchRequest::STATUS_COMPLETED,
            'actual_output' => $this->faker->sentence(8),
            'error_message' => null,
            'processing_time_ms' => $this->faker->numberBetween(100, 5000),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BatchRequest::STATUS_FAILED,
            'actual_output' => null,
            'error_message' => $this->faker->sentence(),
            'processing_time_ms' => $this->faker->numberBetween(100, 1000),
        ]);
    }

    public function withPriority(string $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority,
        ]);
    }

    public function withInput(string $input): static
    {
        return $this->state(fn (array $attributes) => [
            'input' => $input,
        ]);
    }
}
