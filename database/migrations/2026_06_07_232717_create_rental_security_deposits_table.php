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
    Schema::create('rental_security_deposits', function (Blueprint $table) {
        $table->id();
        $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
        $table->string('item_name', 200)->comment('e.g. Jewelry Box, Packing Material');
        $table->decimal('amount', 10, 2)->default(0);
        $table->boolean('is_paid')->default(false)->comment('Did customer pay this deposit?');
        $table->boolean('is_refunded')->default(false);
        $table->timestamp('refunded_at')->nullable();
        $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
        $table->text('note')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();

        $table->index('rental_id');
    });
}

public function down(): void
{
    Schema::dropIfExists('rental_security_deposits');
}
};
