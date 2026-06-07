<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_purchase_order_payments_table
public function up(): void
{
    Schema::create('purchase_order_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
        $table->decimal('amount', 10, 2);
        $table->date('payment_date');
        $table->string('payment_method', 50)->default('cash');
        $table->enum('type', ['payment', 'return_refund'])->default('payment');
        $table->text('note')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();

        $table->index('purchase_order_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_payments');
    }
};
