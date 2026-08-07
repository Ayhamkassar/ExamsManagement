<?php

namespace Database\Factories;

use App\Enums\AcademicYearStatus;
use App\Models\AcademicYear;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2020, 2030);
        $startDate = fake()->dateTimeBetween("$year-01-01", "$year-06-30");
        $endDate = fake()->dateTimeBetween(
            (new \DateTime($startDate->format('Y-m-d')))->modify('+8 months')->format('Y-m-d'),
            ($year + 1).'-12-31'
        );

        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(["$year-$year", "$year-".($year + 1), "AY $year"]),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => AcademicYearStatus::Active,
        ];
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AcademicYearStatus::Upcoming,
            'start_date' => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween(
                (new \DateTime($attributes['start_date']))->modify('+8 months')->format('Y-m-d'),
                '+18 months'
            )->format('Y-m-d'),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AcademicYearStatus::Completed,
            'start_date' => fake()->dateTimeBetween('-2 years', '-1 year')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween(
                (new \DateTime($attributes['start_date']))->modify('+8 months')->format('Y-m-d'),
                '-1 month'
            )->format('Y-m-d'),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AcademicYearStatus::Archived,
        ]);
    }
}
