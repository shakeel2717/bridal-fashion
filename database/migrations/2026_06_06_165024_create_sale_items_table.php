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
    Schema::create('sale_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
        $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

        $table->string('product_name', 200);
        $table->string('product_code', 50);
        $table->decimal('sale_price', 10, 2)->default(0);
        $table->integer('qty')->default(1);

        $table->string('custom_option_label', 200)->nullable();
        $table->decimal('custom_option_price', 10, 2)->default(0);

        $table->text('notes')->nullable();
        $table->timestamps();

        $table->index('sale_id');
        $table->index('product_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
