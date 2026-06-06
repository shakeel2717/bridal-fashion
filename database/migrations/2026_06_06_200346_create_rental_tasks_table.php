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
        Schema::create('rental_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->foreignId('rental_item_id')->nullable()->constrained('rental_items')->cascadeOnDelete();
            $table->string('type', 50)->comment('addon, stitching, custom');
            $table->string('title', 300);
            $table->decimal('cost', 10, 2)->default(0);
            $table->enum('status', ['pending', 'done', 'denied'])->default('pending');
            $table->text('note')->nullable();
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('actioned_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('rental_id');
            $table->index('rental_item_id');
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_tasks');
    }
};
