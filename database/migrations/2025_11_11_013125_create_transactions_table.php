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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('compte_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('XOF');
            $table->timestamp('date');
            $table->string('statut')->default('pending');
            $table->string('reference')->unique();
            $table->foreignUuid('marchand_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index('compte_id');
            $table->index('marchand_id');
            $table->index('reference');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
