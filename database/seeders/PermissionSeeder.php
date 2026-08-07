<?php

namespace Database\Seeders;

use App\Enums\SystemPermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SystemPermission::cases() as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission->value],
                [
                    'name' => str($permission->name)->headline()->toString(),
                    'description' => 'System permission: '.$permission->value,
                ],
            );
        }
    }
}
