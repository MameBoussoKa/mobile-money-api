<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MarchandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Marchand::factory()->create([
            'codeMarchand' => 'MARCHAND-123',
            'nom' => 'Test Marchand',
            'categorie' => 'Services',
            'telephone' => '771234567',
            'adresse' => 'Test Address',
        ]);

        \App\Models\Marchand::factory(4)->create();
    }
}
