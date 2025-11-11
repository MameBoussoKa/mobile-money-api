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
        Schema::create('marchands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->string('codeMarchand')->unique();
            $table->string('categorie');
            $table->string('telephone');
            $table->text('adresse');
            $table->string('qrCode')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('codeMarchand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marchands');
    }
};
