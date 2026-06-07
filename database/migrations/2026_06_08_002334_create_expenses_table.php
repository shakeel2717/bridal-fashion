<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_expenses_table
public function up(): void
{
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('expense_category_id')
              ->constrained('expense_categories')->restrictOnDelete();
        $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
        $table->decimal('amount', 10, 2);
        $table->date('expense_date');
        $table->string('description', 500)->nullable();
        $table->string('reference', 100)->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->index('expense_category_id');
        $table->index('account_id');
        $table->index('expense_date');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
