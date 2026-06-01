<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        // pravatar.cc: avatares realistas por seed numérico, sin API key
        $avatarSeed = fake()->numberBetween(1, 70);
        $avatarUrl  = "https://i.pravatar.cc/150?img={$avatarSeed}";

        $plan = fake()->randomElement(['free', 'free', 'free', 'premium']);

        return [
            'name'              => fake()->firstName().' '.fake()->lastName(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),

            'bio'        => fake()->optional(0.7)->sentence(8),
            'ciudad'     => fake()->randomElement(['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Vigo']),
            'latitud'    => fake()->latitude(40.30, 40.50),
            'longitud'   => fake()->longitude(-3.80, -3.60),
            'plan'       => $plan,
            // El accessor es_premium exige una fecha de expiración futura.
            'plan_expira_at' => $plan === 'premium' ? now()->addMonths(fake()->numberBetween(1, 12)) : null,
            'puntos'     => fake()->numberBetween(0, 500),
            'avatar_url' => $avatarUrl,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
