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
    Schema::create('rental_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
        $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

        // Snapshot
        $table->string('product_name', 200);
        $table->string('product_code', 50);
        $table->decimal('rental_price', 10, 2)->default(0);

        // Custom option on item
        $table->string('custom_option_label', 200)->nullable()->comment('e.g. Adil ki dulhan written on dupatta');
        $table->decimal('custom_option_price', 10, 2)->default(0);

        $table->enum('pickup_status', ['pending', 'picked_up', 'returned'])->default('pending');
        $table->datetime('picked_up_at')->nullable();
        $table->datetime('returned_at')->nullable();
        $table->foreignId('returned_received_by')->nullable()->constrained('users')->nullOnDelete();

        $table->text('notes')->nullable();
        $table->timestamps();

        $table->index('rental_id');
        $table->index('product_id');
        $table->index('pickup_status');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
