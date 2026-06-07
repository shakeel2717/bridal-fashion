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
        Schema::table('rentals', function (Blueprint $table) {
            $table->string('phone1_gender', 10)->default('male')->after('customer_phone1');
            $table->string('phone2_gender', 10)->default('male')->after('customer_phone2');
            $table->string('whatsapp_gender', 10)->default('male')->after('customer_whatsapp');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('phone1_gender', 10)->default('male')->after('customer_phone1');
            $table->string('phone2_gender', 10)->default('male')->after('customer_phone2');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(['phone1_gender', 'phone2_gender', 'whatsapp_gender']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['phone1_gender', 'phone2_gender']);
        });
    }
};
