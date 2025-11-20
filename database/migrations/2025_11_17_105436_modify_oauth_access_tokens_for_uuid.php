<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pour PostgreSQL, nous devons utiliser une approche différente
        DB::statement('ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE VARCHAR(36)');
        DB::statement('ALTER TABLE oauth_access_tokens ALTER COLUMN user_id DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Since the system now uses UUIDs, we can't safely convert back to BIGINT
        // Instead, we'll delete all tokens and recreate the column as unsignedBigInteger
        DB::table('oauth_access_tokens')->delete();

        // Drop and recreate the column as unsignedBigInteger
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
    }
};
