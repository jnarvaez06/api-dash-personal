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
            $table->foreignId('account_id')->after('user_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('related_movement_id')->nullable()->after('category_id')->constrained('movements')->nullOnDelete();
            $table->boolean('is_transfer')->default(false)->after('type');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreignId('category_id')->nullable()->change();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['related_movement_id']);
            $table->dropForeign(['account_id']);
            $table->dropColumn(['account_id', 'related_movement_id', 'is_transfer']);
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }
};
