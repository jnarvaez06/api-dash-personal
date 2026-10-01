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
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['related_movement_id']);
            $table->dropColumn('related_movement_id');
            $table->uuid('transfer_id')->nullable()->after('account_id');
            $table->index('transfer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex(['movements_transfer_id_index']);
            $table->dropColumn('transfer_id');
            $table->foreignId('related_movement_id')->nullable()->after('category_id')->constrained('movements')->nullOnDelete();
        });
    }
};
