<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('permission', ['admin', 'editor', 'viewer']);
            $table->timestamps();
            $table->unique(['inventory_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_user');
    }
};
