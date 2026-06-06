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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('code', 50)->unique();
        $table->string('name', 200);
        $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
        $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
        $table->string('size')->nullable()->comment('waist/size e.g. 28, 30, 32, Free');
        $table->enum('type', ['rental', 'sale', 'both'])->default('rental');
        $table->decimal('purchase_price', 10, 2)->default(0);
        $table->decimal('rental_price', 10, 2)->default(0);
        $table->decimal('sale_price', 10, 2)->default(0);
        $table->integer('stock_qty')->default(1)->comment('Only relevant for sale type');
        $table->boolean('is_abandoned')->default(false);
        $table->decimal('abandoned_price', 10, 2)->default(0)->comment('Written-off value');
        $table->date('abandoned_date')->nullable();
        $table->text('abandoned_note')->nullable();
        $table->text('notes')->nullable();
        $table->boolean('is_active')->default(true);
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->index('code');
        $table->index('category_id');
        $table->index('type');
        $table->index('is_active');
        $table->index('size');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
