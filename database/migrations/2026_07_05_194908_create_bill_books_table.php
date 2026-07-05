<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_books', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('prefix')->nullable();   // e.g. "R", "S", "2024-"
            $table->unsignedInteger('range_from');
            $table->unsignedInteger('range_to');
            $table->enum('type', ['rental', 'sale', 'both'])->default('both');
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['range_from', 'range_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_books');
    }
};