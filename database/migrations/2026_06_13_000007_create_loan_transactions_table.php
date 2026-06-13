<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lender_id')->constrained('lenders')->cascadeOnDelete();
            $table->enum('type', ['received', 'paid'])->comment('received = borrowed from them, paid = paid them back');
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2)->default(0)->comment('running outstanding balance after this txn');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->date('date');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_transactions');
    }
};
