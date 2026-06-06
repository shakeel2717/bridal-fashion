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
    Schema::create('rentals', function (Blueprint $table) {
        $table->id();
        $table->string('bill_ref', 50)->nullable()->comment('Physical bill book reference number');
        $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

        // Snapshot fields (in case customer info changes)
        $table->string('customer_name', 150);
        $table->string('customer_phone1', 20);
        $table->string('customer_phone2', 20)->nullable();
        $table->string('customer_whatsapp', 20)->nullable();
        $table->string('customer_cnic', 20)->nullable();
        $table->text('delivery_address')->nullable();

        $table->date('booking_date');
        $table->date('pickup_date')->nullable();
        $table->date('return_date')->nullable();
        $table->date('stitching_date')->nullable();
        $table->text('stitching_instructions')->nullable();

        $table->enum('status', [
            'booked',
            'ready',
            'picked_up',
            'partially_picked_up',
            'returned',
            'cancelled',
            'abandoned'
        ])->default('booked');

        $table->decimal('total_amount', 10, 2)->default(0);
        $table->decimal('advance_paid', 10, 2)->default(0);
        $table->decimal('remaining_balance', 10, 2)->default(0);
        $table->decimal('refund_amount', 10, 2)->default(0);
        $table->enum('refund_type', ['full', 'partial', 'none'])->nullable();
        $table->date('refund_date')->nullable();
        $table->text('refund_note')->nullable();

        $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete()->comment('Employee who handled deal');
        $table->text('notes')->nullable();

        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->index('bill_ref');
        $table->index('customer_id');
        $table->index('status');
        $table->index('booking_date');
        $table->index('return_date');
        $table->index('employee_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
