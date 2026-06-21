<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // For SQLite (dev): enum is stored as text, accepts any value
            // For MySQL (prod): we need to modify the enum
            $table->string('fabric_unit', 10)->nullable()->after('stock_qty')
                ->comment('meter or gaz — only for fabric type');

            $table->decimal('stock_decimal', 10, 3)->default(0)->after('fabric_unit')
                ->comment('Decimal stock for fabric type');
        });

        // MySQL only: alter the enum to add new values
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('rental','sale','both','service','fabric') DEFAULT 'rental'");
        }
        // SQLite: enum is just a string column — no change needed
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['fabric_unit', 'stock_decimal']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('rental','sale','both') DEFAULT 'rental'");
        }
    }
};
