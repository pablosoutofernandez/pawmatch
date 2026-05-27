<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Perro>
 */
class PerroFactory extends Factory
{
    public function definition(): array
    {
        $razas = [
            'Golden Retriever', 'Labrador Retriever', 'Beagle', 'Border Collie',
            'Pastor Alemán', 'Bulldog Francés', 'Caniche', 'Husky Siberiano',
            'Chihuahua', 'Yorkshire Terrier', 'Boxer', 'Dálmata',
        ];

        $rasgos = ['jugueton', 'tranquilo', 'amigable', 'timido', 'energico', 'cariñoso', 'protector'];

        return [
            'user_id'             => User::factory(),
            'nombre'              => fake()->firstName(),
            'raza'                => fake()->randomElement($razas),
            'edad_anios'          => fake()->numberBetween(1, 12),
            'peso_kg'             => fake()->randomFloat(2, 4, 45),
            'sexo'                => fake()->randomElement(['macho', 'hembra']),
            'esterilizado'        => fake()->boolean(70),
            'energia'             => fake()->numberBetween(2, 5),
            'caracter'            => fake()->randomElements($rasgos, fake()->numberBetween(1, 3)),
            'vacunado'            => fake()->boolean(85),
            'compatible_pequenos' => fake()->boolean(80),
            'compatible_grandes'  => fake()->boolean(80),
            'notas'               => fake()->optional(0.4)->sentence(),
        ];
    }
}
