<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos básicos PawMatch
        Permission::firstOrCreate(['name' => 'ver']);
        Permission::firstOrCreate(['name' => 'crear']);
        Permission::firstOrCreate(['name' => 'editar']);
        Permission::firstOrCreate(['name' => 'borrar']);

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $premium = Role::firstOrCreate(['name' => 'premium']);
        $premium->givePermissionTo(['ver', 'crear', 'editar', 'borrar']);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo(['ver', 'crear', 'editar']);

        $guest = Role::firstOrCreate(['name' => 'guest']);
        $guest->givePermissionTo(['ver']);
    }
}
