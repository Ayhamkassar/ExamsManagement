<?php

namespace Database\Factories;

use App\Enums\ExaminationSessionStatus;
use App\Models\ExaminationSession;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExaminationSession>
 */
class ExaminationSessionFactory extends Factory
{
    protected $model = ExaminationSession::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+1 month');
        $endDate = (clone $startDate)->modify('+' . fake()->numberBetween(2, 4) . ' hours');

        return [
            'tenant_id' => Tenant::factory(),
            'examination_id' => \App\Models\Examination::factory(),
            'session_code' => fake()->unique()->bothify('SES-####'),
            'scheduled_start_at' => $startDate,
            'scheduled_end_at' => $endDate,
            'timezone' => fake()->timezone(),
            'location_name' => fake()->optional()->randomElement([
                'Main Hall A', 'Main Hall B', 'Room 101', 'Room 205',
                'Auditorium', 'Conference Hall', 'Building C - Floor 2',
            ]),
            'location_metadata' => fake()->optional()->randomElements([
                'building' => 'Building A',
                'floor' => fake()->numberBetween(1, 5),
                'capacity' => fake()->numberBetween(50, 200),
                'accessibility_features' => ['elevator', 'ramp'],
            ], 2),
            'status' => ExaminationSessionStatus::Scheduled,
            'capacity' => fake()->optional()->numberBetween(50, 200),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationSessionStatus::Scheduled,
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationSessionStatus::Open,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationSessionStatus::InProgress,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationSessionStatus::Completed,
            'scheduled_end_at' => now()->subHour(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationSessionStatus::Cancelled,
        ]);
    }
}
