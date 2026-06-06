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
    Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->string('bill_ref', 50)->nullable();
        $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

        $table->string('customer_name', 150);
        $table->string('customer_phone1', 20);
        $table->string('customer_phone2', 20)->nullable();
        $table->string('customer_cnic', 20)->nullable();
        $table->text('delivery_address')->nullable();

        $table->date('sale_date');
        $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('completed');

        $table->decimal('total_amount', 10, 2)->default(0);
        $table->decimal('advance_paid', 10, 2)->default(0);
        $table->decimal('remaining_balance', 10, 2)->default(0);
        $table->decimal('refund_amount', 10, 2)->default(0);
        $table->date('refund_date')->nullable();
        $table->text('refund_note')->nullable();

        $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
        $table->text('notes')->nullable();

        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->index('bill_ref');
        $table->index('customer_id');
        $table->index('status');
        $table->index('sale_date');
        $table->index('employee_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
