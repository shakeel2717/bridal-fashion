<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->string('item_code')->nullable();
            $table->integer('qty_returned')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0); // original sale price
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('reason')->nullable();     // damage, wrong_item, changed_mind, other
            $table->string('condition')->nullable();  // good, damaged (affects stock restore)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
