<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained();
            $table->date('return_date');
            $table->decimal('total_amount', 12, 2)->default(0);
            // resolution: how vendor resolves the return
            $table->enum('resolution', ['pending', 'refund', 'replacement'])->default('pending');
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->date('refund_date')->nullable();
            $table->foreignId('refund_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            // status: state of the return process
            $table->enum('status', ['pending', 'sent', 'resolved'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
