<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'tenant_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(fn () => [
            'is_system' => true,
        ]);
    }

    public function forTenant(string $tenantId): static
    {
        return $this->state(fn () => [
            'tenant_id' => $tenantId,
        ]);
    }
}
