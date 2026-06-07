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
            $table->string('walkin_photo')->nullable()->after('delivery_address');
            $table->string('walkin_cnic_front')->nullable()->after('walkin_photo');
            $table->string('walkin_cnic_back')->nullable()->after('walkin_cnic_front');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(['walkin_photo', 'walkin_cnic_front', 'walkin_cnic_back']);
        });
    }
};
