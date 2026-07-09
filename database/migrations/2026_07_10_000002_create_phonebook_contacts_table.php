<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phonebook_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phonebook_category_id')
                  ->nullable()
                  ->constrained('phonebook_categories')
                  ->nullOnDelete();
            $table->string('name', 100);
            $table->json('phone_numbers');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('phonebook_category_id');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phonebook_contacts');
    }
};
