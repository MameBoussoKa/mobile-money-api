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
            'telephone' => '785942490',
            'email' => 'kamamediarra2002@gmail.com',
            'confirmation_code' => '123456',
            'email_verified_at' => now(),
        ]);

        $compte = new Compte([
            'numeroCompte' => 'CMPT-TEST001',
            'devise' => 'XOF',
            'dateDerniereMaj' => now(),
        ]);
        $compte->id = (string) \Illuminate\Support\Str::uuid();
        $client->compte()->save($compte);

        // Add initial balance transaction
        \App\Models\Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'deposit',
            'montant' => 1000.00,
            'devise' => 'XOF',
            'date' => now(),
            'statut' => 'completed',
            'reference' => 'DEP-INIT',
        ]);

        // Create additional test client for payment testing
        $testUser = User::create([
            'username' => 'recipient',
            'password' => bcrypt('password123'),
            'role' => 'client',
            'langue' => 'fr',
            'theme' => 'light',
        ]);

        $testClient = $testUser->client()->create([
            'nom' => 'Recipient',
            'prenom' => 'Test',
            'telephone' => '775942490',
            'email' => 'recipient@test.com',
            'confirmation_code' => '123456',
            'email_verified_at' => now(),
        ]);

        $testCompte = new Compte([
            'numeroCompte' => 'CMPT-RECIPIENT',
            'devise' => 'XOF',
            'dateDerniereMaj' => now(),
        ]);
        $testCompte->id = (string) \Illuminate\Support\Str::uuid();
        $testClient->compte()->save($testCompte);

        // Create test marchand
        $marchandUser = User::create([
            'username' => 'marchand',
            'password' => bcrypt('password123'),
            'role' => 'marchand',
            'langue' => 'fr',
            'theme' => 'light',
        ]);

        $marchand = new \App\Models\Marchand([
            'nom' => 'Test Marchand',
            'codeMarchand' => 'MARCHAND-123',
            'categorie' => 'Commerce',
            'telephone' => '771234567',
            'adresse' => 'Test Address',
            'qrCode' => null,
        ]);
        $marchand->id = (string) \Illuminate\Support\Str::uuid();
        $marchandUser->marchand()->save($marchand);

        $this->call([
            ClientSeeder::class,
            CompteSeeder::class,
            MarchandSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
