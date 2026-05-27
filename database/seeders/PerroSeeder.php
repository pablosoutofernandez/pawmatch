<?php

namespace Database\Seeders;

use App\Models\Perro;
use App\Models\User;
use Illuminate\Database\Seeder;

class PerroSeeder extends Seeder
{
    public function run(): void
    {
        // Perro del admin demo (Lola)
        $admin = User::where('email', 'admin@pawmatch.test')->first();
        if ($admin) {
            Perro::create([
                'user_id'             => $admin->id,
                'nombre'              => 'Lola',
                'raza'                => 'Golden Retriever',
                'edad_anios'          => 2,
                'peso_kg'             => 28,
                'sexo'                => 'hembra',
                'esterilizado'        => true,
                'vacunado'            => true,
                'energia'             => 4,
                'caracter'            => ['jugueton', 'amigable', 'cariñoso'],
                'compatible_pequenos' => true,
                'compatible_grandes'  => true,
            ]);
        }

        // Perro del user demo (Rocky)
        $user = User::where('email', 'user@pawmatch.test')->first();
        if ($user) {
            Perro::create([
                'user_id'             => $user->id,
                'nombre'              => 'Rocky',
                'raza'                => 'Labrador Retriever',
                'edad_anios'          => 3,
                'peso_kg'             => 32,
                'sexo'                => 'macho',
                'esterilizado'        => true,
                'vacunado'            => true,
                'energia'             => 5,
                'caracter'            => ['jugueton', 'energico'],
                'compatible_pequenos' => true,
                'compatible_grandes'  => true,
            ]);
        }

        // 1 perro por cada usuario restante (los que no tengan)
        User::whereDoesntHave('perros')->get()->each(function (User $u) {
            Perro::factory()->create(['user_id' => $u->id]);
        });

        $this->command->info('✅ Perros creados.');
    }
}
