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
        Schema::table('employees', function (Blueprint $table) {
            // Datos laborales adicionales
            $table->string('position')->nullable()->after('manager_name');
            $table->decimal('salary_gross', 12, 2)->nullable()->after('position');
            $table->decimal('salary_net', 12, 2)->nullable()->after('salary_gross');
            $table->string('salary_period', 30)->nullable()->after('salary_net');

            // Beneficiario único — para soporte multi-beneficiario hace falta tabla aparte
            $table->string('beneficiary_name')->nullable()->after('emergency_contact_relationship');
            $table->string('beneficiary_relationship', 50)->nullable()->after('beneficiary_name');
            $table->string('beneficiary_phone', 30)->nullable()->after('beneficiary_relationship');
            $table->decimal('beneficiary_percentage', 5, 2)->default(100)->after('beneficiary_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'position',
                'salary_gross',
                'salary_net',
                'salary_period',
                'beneficiary_name',
                'beneficiary_relationship',
                'beneficiary_phone',
                'beneficiary_percentage',
            ]);
        });
    }
};
