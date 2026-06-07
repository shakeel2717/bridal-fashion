<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // add_group_id_to_products_table
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('category_id')
                ->constrained('product_groups')->nullOnDelete();
            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
