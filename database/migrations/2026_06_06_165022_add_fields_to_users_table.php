<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_fields_to_users_table.php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->enum('role', ['admin', 'employee'])->default('employee')->after('email');
        $table->string('phone', 20)->nullable()->after('role');
        $table->string('cnic', 20)->nullable()->after('phone');
        $table->text('address')->nullable()->after('cnic');
        $table->string('photo')->nullable()->after('address');
        $table->string('designation')->nullable()->after('photo');
        $table->date('joining_date')->nullable()->after('designation');
        $table->enum('salary_type', ['monthly', 'daily'])->default('monthly')->after('joining_date');
        $table->decimal('salary_amount', 10, 2)->default(0)->after('salary_type');
        $table->boolean('is_active')->default(true)->after('salary_amount');
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('is_active');
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropSoftDeletes();
        $table->dropColumn([
            'role', 'phone', 'cnic', 'address', 'photo',
            'designation', 'joining_date', 'salary_type',
            'salary_amount', 'is_active', 'created_by', 'updated_by'
        ]);
    });
}
};
