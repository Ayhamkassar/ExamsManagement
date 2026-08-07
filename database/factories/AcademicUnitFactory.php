<?php

namespace Database\Factories;

use App\Enums\AcademicUnitType;
use App\Models\AcademicUnit;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicUnit>
 */
class AcademicUnitFactory extends Factory
{
    protected $model = AcademicUnit::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'parent_id' => null,
            'type' => fake()->randomElement(AcademicUnitType::cases())->value,
            'name' => fake()->randomElement(['Grade 1', 'Grade 2', 'Faculty of Science', 'Department of Computer Science', 'Program A', 'Level I']).' '.fake()->unique()->numerify('##'),
            'code' => fake()->unique()->optional()->bothify('???###'),
            'metadata' => fake()->optional()->randomElements(['level' => fake()->numberBetween(1, 5), 'credits' => fake()->numberBetween(1, 10)], 2),
            'status' => 'active',
        ];
    }

    public function faculty(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AcademicUnitType::Faculty->value,
            'name' => fake()->randomElement(['Faculty of Engineering', 'Faculty of Sciences', 'Faculty of Arts', 'Faculty of Medicine']).' '.fake()->unique()->numerify('##'),
        ]);
    }

    public function department(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AcademicUnitType::Department->value,
            'name' => fake()->randomElement(['Computer Science', 'Mathematics', 'Physics', 'Chemistry', 'Biology']).' Department',
        ]);
    }

    public function grade(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AcademicUnitType::Grade->value,
            'name' => fake()->randomElement(['Grade 1', 'Grade 5', 'Grade 9', 'Grade 10', 'Grade 12']),
        ]);
    }

    public function classRoom(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AcademicUnitType::ClassRoom->value,
            'name' => fake()->randomElement(['A', 'B', 'C', 'D']).' '.fake()->numerify('##'),
        ]);
    }

    public function withParent(AcademicUnit $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'tenant_id' => $parent->tenant_id,
        ]);
    }
}
