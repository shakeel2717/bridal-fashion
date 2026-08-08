<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['advance', 'salary_deduction', 'cash_repayment']);
            $table->decimal('amount', 10, 2);
            $table->date('ledger_date');
            $table->text('note')->nullable();
            $table->foreignId('advance_id')->nullable()->constrained('advances')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('ledger_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_ledger');
    }
};
