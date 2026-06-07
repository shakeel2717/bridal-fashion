<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_purchase_orders_table
public function up(): void
{
    Schema::create('purchase_orders', function (Blueprint $table) {
        $table->id();
        $table->string('po_number', 50)->unique()->nullable();
        $table->string('vendor_bill_number', 100)->nullable();
        $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
        $table->date('order_date');
        $table->date('expected_date')->nullable();
        $table->date('received_date')->nullable();
        $table->enum('status', ['draft', 'ordered', 'received', 'partial', 'cancelled', 'returned'])
              ->default('draft');
        $table->decimal('total_amount', 10, 2)->default(0);
        $table->decimal('amount_paid', 10, 2)->default(0);
        $table->decimal('balance_due', 10, 2)->default(0);
        $table->decimal('discount', 10, 2)->default(0);
        $table->text('notes')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->index('vendor_id');
        $table->index('status');
        $table->index('order_date');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
