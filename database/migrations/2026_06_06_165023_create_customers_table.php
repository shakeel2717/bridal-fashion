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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name', 150);
        $table->string('phone1', 20);
        $table->string('phone2', 20)->nullable();
        $table->string('whatsapp', 20)->nullable();
        $table->string('cnic', 20)->nullable();
        $table->string('photo')->nullable();
        $table->text('address')->nullable();
        $table->text('notes')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->index('phone1');
        $table->index('cnic');
        $table->index('name');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
