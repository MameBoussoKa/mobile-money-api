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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, set unique emails for clients with null email
        $clientsWithoutEmail = DB::table('clients')->whereNull('email')->get();

        foreach ($clientsWithoutEmail as $client) {
            $uniqueEmail = 'temp_' . $client->id . '@example.com';
            DB::table('clients')->where('id', $client->id)->update(['email' => $uniqueEmail]);
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
