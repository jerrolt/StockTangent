<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('status', [
                'pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded',
            ])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
