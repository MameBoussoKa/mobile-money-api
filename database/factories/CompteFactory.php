<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
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
            'client_id' => \App\Models\Client::factory(),
            'numeroCompte' => fake()->unique()->numerify('##########'),
            'solde' => fake()->randomFloat(2, 0, 10000),
            'devise' => 'XOF',
            'dateDerniereMaj' => now(),
        ];
    }
}
