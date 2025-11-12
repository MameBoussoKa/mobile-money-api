<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Compte;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::create([
            'username' => 'test',
            'password' => bcrypt('password123'),
            'role' => 'client',
            'langue' => 'fr',
            'theme' => 'light',
        ]);

        $client = $user->client()->create([
            'nom' => 'Test',
            'prenom' => 'User',
            'telephone' => '1234567890',
            'email' => 'kamamediarra2002@gmail.com',
            'confirmation_code' => '123456',
            'email_verified_at' => now(),
        ]);

        $compte = new Compte([
            'numeroCompte' => 'CMPT-TEST001',
            'solde' => 1000.00,
            'devise' => 'XOF',
            'dateDerniereMaj' => now(),
        ]);
        $compte->id = (string) \Illuminate\Support\Str::uuid();
        $client->compte()->save($compte);

        $this->call([
            ClientSeeder::class,
            CompteSeeder::class,
            MarchandSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
