<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerroFactory extends Factory
{
    public function definition(): array
    {
        $razas = [
            'Golden Retriever', 'Labrador Retriever', 'Beagle', 'Border Collie',
            'Pastor Alemán', 'Bulldog Francés', 'Caniche', 'Husky Siberiano',
            'Chihuahua', 'Yorkshire Terrier', 'Boxer', 'Dálmata',
        ];

        $rasgos = ['juguetón', 'tranquilo', 'amigable', 'tímido', 'enérgico', 'cariñoso', 'protector', 'curioso', 'independiente', 'sociable'];

        $descripciones = [
            'Le encanta correr por el parque y nunca se cansa de buscar la pelota. Se lleva genial con todo el mundo.',
            'Es muy tranquilo y le van los paseos largos sin prisas. Perfecto para quienes disfrutan de caminar sin prisa.',
            'Un poco tímido al principio, pero una vez que te conoce es todo amor. Adora los juegos de olfato.',
            'Lleno de energía por las mañanas. Si tu perro también madruga, os vais a llevar de maravilla.',
            'Le chiflan los charcos y volver a casa lleno de barro. Un alma libre que disfruta de la naturaleza.',
            'Super sociable con perros de cualquier tamaño. Nunca ha tenido un mal encuentro en el parque.',
            'Muy inteligente, aprende trucos con facilidad. Busca compañeros que también les guste explorar rutas nuevas.',
            'Calmado y afectuoso. Le va bien un paseo tranquilo por la tarde y mucho tiempo de olfateo libre.',
            'Pura energía en un cuerpo pequeño. No se deja intimidar por perros más grandes y siempre quiere jugar.',
            'Muy curioso con todo lo que huele. Le encanta explorar, así que los parques con mucha naturaleza son su favorito.',
        ];

        $raza = fake()->randomElement($razas);

        // Foto de demo: URL remota (placedog.net) guardada como si fuese una
        // foto subida. Los perros reales sin foto caen al "misterioso".
        $fotoSeed   = fake()->numberBetween(1, 200);
        $fotoRemota = 'https://placedog.net/400/400?id='.$fotoSeed;

        return [
            'user_id'             => User::factory(),
            'nombre'              => fake()->firstName(),
            'raza'                => $raza,
            'edad_anios'          => fake()->numberBetween(1, 12),
            'peso_kg'             => fake()->randomFloat(2, 4, 45),
            'sexo'                => fake()->randomElement(['macho', 'hembra']),
            'esterilizado'        => fake()->boolean(70),
            'energia'             => fake()->numberBetween(2, 5),
            'caracter'            => fake()->randomElements($rasgos, fake()->numberBetween(2, 4)),
            'vacunado'            => fake()->boolean(85),
            'notas'               => fake()->optional(0.4)->sentence(),
            'descripcion'         => fake()->randomElement($descripciones),
            'foto_principal'      => $fotoRemota,
        ];
    }
}
