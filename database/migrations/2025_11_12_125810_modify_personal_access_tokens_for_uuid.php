<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pour PostgreSQL, utiliser SQL brut pour éviter les problèmes de conversion
        DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE VARCHAR(36)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all tokens since they are for uuid users
        DB::table('personal_access_tokens')->delete();

        // Drop and recreate the column as unsignedBigInteger
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('tokenable_id');
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('tokenable_id');
        });
    }
};
