<?php

namespace Database\Factories;

use App\Enums\ExaminationStatus;
use App\Models\Examination;
use App\Models\ExaminationCycle;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Examination>
 */
class ExaminationFactory extends Factory
{
    protected $model = Examination::class;

    public function definition(): array
    {
        $cycle = ExaminationCycle::factory();

        return [
            'tenant_id' => Tenant::factory(),
            'examination_cycle_id' => ExaminationCycle::factory(),
            'subject_id' => \App\Models\Subject::factory(),
            'academic_unit_id' => fake()->optional(0.6)->randomElement(\App\Models\AcademicUnit::all()->pluck('id')->toArray() ?? []),
            'name' => fake()->randomElement([
                'Mathematics', 'Physics', 'Chemistry', 'Biology',
                'Arabic Language', 'English Language', 'French Language',
                'Computer Science', 'History', 'Geography',
            ]),
            'code' => fake()->unique()->bothify('SUBJ-####'),
            'description' => fake()->optional()->sentence(),
            'duration_minutes' => fake()->numberBetween(60, 180),
            'total_marks' => fake()->numberBetween(20, 100),
            'passing_marks' => fake()->optional(0.7)->numberBetween(10, 50),
            'status' => ExaminationStatus::Draft,
            'configuration' => fake()->optional()->randomElements([
                'correction_mode' => 'single_reviewer',
                'grading_scheme' => 'percentage',
                'anonymous_correction' => false,
            ], 2),
            'created_by' => User::factory(),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationStatus::Scheduled,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationStatus::Active,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExaminationStatus::Completed,
        ]);
    }
}
