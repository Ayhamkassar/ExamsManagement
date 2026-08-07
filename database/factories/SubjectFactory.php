<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $subjectNames = [
            'Mathematics', 'Physics', 'Chemistry', 'Biology', 'English',
            'History', 'Geography', 'Computer Science', 'Literature', 'Art',
            'Music', 'Physical Education', 'Economics', 'Accounting', 'Statistics',
        ];

        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->randomElement($subjectNames),
            'code' => fake()->unique()->optional()->bothify('???###'),
            'description' => fake()->optional()->sentence(),
            'metadata' => fake()->optional()->randomElements(['credits' => fake()->numberBetween(1, 5), 'hours_per_week' => fake()->numberBetween(2, 6)], 2),
            'status' => 'active',
        ];
    }
}
