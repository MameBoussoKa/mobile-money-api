<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Marchand>
 */
class MarchandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => \App\Models\User::factory(),
            'nom' => fake()->company(),
            'codeMarchand' => fake()->unique()->numerify('M######'),
            'categorie' => fake()->randomElement(['Alimentation', 'Transport', 'Services', 'Commerce']),
            'telephone' => fake()->phoneNumber(),
            'adresse' => fake()->address(),
            'qrCode' => null,
        ];
    }
}
