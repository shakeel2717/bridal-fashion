<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phonebook_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('color', 7)->default('#4f46e5');
            $table->string('icon', 60)->default('bi-person-fill');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phonebook_categories');
    }
};
