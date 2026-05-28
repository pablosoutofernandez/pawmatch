<?php

namespace Database\Seeders;

use App\Models\Perro;
use App\Models\User;
use Illuminate\Database\Seeder;

class PerroSeeder extends Seeder
{
    public function run(): void
    {
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
                'caracter'            => ['juguetona', 'amigable', 'cariñosa'],
                'compatible_pequenos' => true,
                'compatible_grandes'  => true,
                'descripcion'         => 'Lola es pura alegría. Le encanta el agua, las pelotas y conocer perros nuevos. Salimos cada mañana por el Retiro y buscamos compañeros de aventura.',
                'foto_principal'      => 'https://place.dog/400/300?random=42',
            ]);
        }

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
                'caracter'            => ['juguetón', 'enérgico', 'sociable'],
                'compatible_pequenos' => true,
                'compatible_grandes'  => true,
                'descripcion'         => 'Rocky tiene energía de sobra y le hacen falta compañeros que lo aguanten. Ideal para rutas largas, carreras por el parque o simplemente explorar nuevos caminos juntos.',
                'foto_principal'      => 'https://place.dog/400/300?random=77',
            ]);
        }

        // Resto de usuarios sin perro
        User::whereDoesntHave('perros')->get()->each(function (User $u) {
            Perro::factory()->create(['user_id' => $u->id]);
        });

        $this->command->info('✅ Perros creados.');
    }
}
