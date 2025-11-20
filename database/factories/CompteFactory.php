<?php

namespace Database\Factories;

use App\Models\Compte;
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
            'devise' => 'XOF',
            'dateDerniereMaj' => now(),
        ];
    }

    public function withSolde($amount = 0)
    {
        return $this->afterCreating(function (Compte $compte) use ($amount) {
            if ($amount > 0) {
                // Create an initial transaction to set the balance
                \App\Models\Transaction::factory()->create([
                    'compte_id' => $compte->id,
                    'type' => 'deposit',
                    'montant' => $amount,
                    'statut' => 'completed',
                ]);
            }
        });
    }
}
