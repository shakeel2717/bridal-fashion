<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_transactions_table
public function up(): void
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
        $table->enum('type', ['credit', 'debit']);
        // credit = money IN, debit = money OUT
        $table->decimal('amount', 10, 2);
        $table->decimal('balance_after', 10, 2)->default(0);
        $table->string('category', 100)->nullable();
        // rental_payment, sale_payment, expense, salary,
        // vendor_payment, transfer_in, transfer_out,
        // owner_withdrawal, opening_balance, other
        $table->string('description', 500)->nullable();
        $table->date('transaction_date');

        // Optional references
        $table->nullableMorphs('referenceable');
        // links to Rental, Sale, Expense, SalaryRecord etc.

        $table->foreignId('transfer_to_account_id')->nullable()
              ->constrained('accounts')->nullOnDelete();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();

        $table->index('account_id');
        $table->index('type');
        $table->index('category');
        $table->index('transaction_date');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
