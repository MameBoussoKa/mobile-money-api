<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'compte_id' => \App\Models\Compte::factory(),
            'type' => fake()->randomElement(['debit', 'credit', 'transfer']),
            'montant' => fake()->randomFloat(2, 10, 1000),
            'devise' => 'XOF',
            'date' => now(),
            'statut' => 'completed',
            'reference' => fake()->unique()->uuid(),
            'marchand_id' => \App\Models\Marchand::factory(),
        ];
    }
}
