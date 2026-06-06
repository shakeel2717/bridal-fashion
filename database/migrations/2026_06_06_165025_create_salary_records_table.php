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
    Schema::create('salary_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->integer('month')->comment('1-12');
        $table->integer('year');
        $table->decimal('base_salary', 10, 2)->default(0);
        $table->integer('days_present')->default(0);
        $table->decimal('earned_salary', 10, 2)->default(0);
        $table->decimal('total_advances', 10, 2)->default(0);
        $table->decimal('total_bonus', 10, 2)->default(0);
        $table->decimal('net_salary', 10, 2)->default(0);
        $table->date('paid_date')->nullable();
        $table->enum('status', ['draft', 'paid'])->default('draft');
        $table->text('note')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->unique(['user_id', 'month', 'year']);
        $table->index('status');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_records');
    }
};
