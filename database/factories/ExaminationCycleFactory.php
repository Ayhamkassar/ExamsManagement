<?php

namespace Database\Factories;

use App\Enums\ExaminationCycleStatus;
use App\Enums\ExaminationCycleType;
use App\Models\ExaminationCycle;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExaminationCycle>
 */
class ExaminationCycleFactory extends Factory
{
    protected $model = ExaminationCycle::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2024, 2030);

        return [
            'tenant_id' => Tenant::factory(),
            'academic_year_id' => fake()->optional(0.7)->randomElement(\App\Models\AcademicYear::all()->pluck('id')->toArray() ?? []),
            'name' => fake()->randomElement([
                "$year Grade 9 National Examination",
                "$year Baccalaureate Examination",
                "$year University Final Examinations",
                "$year Certification Examination",
            ]),
            'code' => fake()->unique()->bothify('EXAM-####'),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(ExaminationCycleType::cases())->value,
            'status' => ExaminationCycleStatus::Draft,
            'start_date' => fake()->optional(0.8)->date("$year-01-01", "$year-06-30"),
            'end_date' => fake()->optional(0.8)->date("$year-07-01", "$year-12-31"),
            'metadata' => fake()->optional()->randomElements([
                'registration_start' => fake()->date(),
                'registration_end' => fake()->date(),
                'total_candidates' => fake()->numberBetween(100, 10000),
            ], 2),
            'created_by' => User::factory(),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationCycleStatus::Scheduled,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationCycleStatus::Active,
            'start_date' => now()->subMonths(1)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationCycleStatus::Completed,
            'start_date' => now()->subMonths(6)->format('Y-m-d'),
            'end_date' => now()->subMonths(3)->format('Y-m-d'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationCycleStatus::Cancelled,
        ]);
    }
}
