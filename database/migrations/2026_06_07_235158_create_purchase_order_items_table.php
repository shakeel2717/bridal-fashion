<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_purchase_order_items_table
public function up(): void
{
    Schema::create('purchase_order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
        $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
        $table->string('item_name', 200);
        $table->string('item_code', 50)->nullable();
        $table->integer('qty')->default(1);
        $table->decimal('unit_price', 10, 2)->default(0);
        $table->decimal('total_price', 10, 2)->default(0);
        $table->integer('received_qty')->default(0);
        $table->integer('returned_qty')->default(0);
        $table->text('notes')->nullable();
        $table->timestamps();

        $table->index('purchase_order_id');
        $table->index('product_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
