<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 14, 4);
            $table->char('currency', 3)->default('USD');
            $table->timestamp('observed_at')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'store_id', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_store');
    }
};
