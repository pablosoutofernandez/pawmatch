<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin demo
        $admin = User::create([
            'name'              => 'María García',
            'email'             => 'admin@pawmatch.test',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'bio'               => 'Mamá de Lola, una Golden de 2 años. Salimos todos los días por el Retiro 🐾',
            'ciudad'            => 'Salamanca, Madrid',
            'latitud'           => 40.4168,
            'longitud'          => -3.7038,
            'plan'              => 'premium',
            'plan_expira_at'    => now()->addYear(),
            'puntos'            => 320,
            'avatar_url'        => 'https://i.pravatar.cc/150?img=5',
        ]);
        $admin->assignRole('admin');

        // Usuario normal demo
        $user = User::create([
            'name'              => 'Carlos Méndez',
            'email'             => 'user@pawmatch.test',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'ciudad'            => 'Chamberí, Madrid',
            'latitud'           => 40.4280,
            'longitud'          => -3.7100,
            'plan'              => 'free',
            'puntos'            => 50,
            'avatar_url'        => 'https://i.pravatar.cc/150?img=12',
        ]);
        $user->assignRole('user');

        // 8 usuarios aleatorios con roles aleatorios
        $roles = ['user', 'user', 'user', 'premium'];
        User::factory(8)->create()->each(function (User $u) use ($roles) {
            $u->assignRole(fake()->randomElement($roles));
        });

        $this->command->info('✅ Usuarios creados.');
        $this->command->info('   Admin:   admin@pawmatch.test / password');
        $this->command->info('   Usuario: user@pawmatch.test / password');
    }
}
