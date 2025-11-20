<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Client::factory()->create([
            'telephone' => '775942400',
            'nom' => 'Test',
            'prenom' => 'Recipient',
            'email' => 'recipient@example.com',
            'email_verified_at' => now(),
        ]);

        \App\Models\Client::factory(9)->create();
    }
}
