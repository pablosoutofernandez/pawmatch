<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->firstName().' '.fake()->lastName(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),

            'bio'      => fake()->optional(0.7)->sentence(8),
            'ciudad'   => fake()->randomElement(['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Vigo']),
            'latitud'  => fake()->latitude(40.30, 40.50),
            'longitud' => fake()->longitude(-3.80, -3.60),
            'plan'     => fake()->randomElement(['free', 'free', 'free', 'premium']),
            'puntos'   => fake()->numberBetween(0, 500),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
