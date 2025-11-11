<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained()->onDelete('cascade');
            $table->string('numeroCompte')->unique();
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise', 3)->default('XOF');
            $table->timestamp('dateDerniereMaj')->nullable();
            $table->timestamps();

            $table->index('client_id');
            $table->index('numeroCompte');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
