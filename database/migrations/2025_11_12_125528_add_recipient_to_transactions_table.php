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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('recipient_type')->nullable()->after('marchand_id');
            $table->foreignUuid('recipient_id')->nullable()->after('recipient_type');
            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['recipient_type', 'recipient_id']);
            $table->dropColumn(['recipient_type', 'recipient_id']);
        });
    }
};
