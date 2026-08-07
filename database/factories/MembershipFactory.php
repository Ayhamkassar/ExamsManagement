<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition(): array
    {
        return [
            'organization_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'role_id' => null,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
            'invited_by' => null,
        ];
    }

    public function invited(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Invited,
            'joined_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Suspended,
        ]);
    }

    public function removed(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Removed,
        ]);
    }
}
