<?php

namespace Database\Factories;

use App\Enums\OrganizationType;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'legal_name' => $name.' Ltd.',
            'type' => OrganizationType::School,
            'status' => TenantStatus::Active,
            'settings' => [],
        ];
    }
}
