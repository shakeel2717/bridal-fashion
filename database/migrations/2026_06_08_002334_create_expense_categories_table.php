<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // create_expense_categories_table
public function up(): void
{
    Schema::create('expense_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name', 150);
        $table->string('color', 20)->default('#718096');
        $table->foreignId('parent_id')->nullable()
              ->constrained('expense_categories')->nullOnDelete();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
