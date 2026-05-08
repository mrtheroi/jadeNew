<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('employee_number', 50)->unique();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('curp', 18)->nullable();

            // Datos personales
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('nationality')->default('Mexicana');
            $table->unsignedTinyInteger('children_count')->default(0);
            $table->text('address')->nullable();

            // Datos laborales
            $table->string('business_unit', 50);
            $table->string('department')->nullable();
            $table->string('manager_name')->nullable();
            $table->date('hired_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('terminated_at')->nullable();

            // Contacto de emergencia
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable();

            $table->timestamps();

            // Índices para filtros y búsqueda
            $table->index('business_unit');
            $table->index('is_active');
            $table->index('full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
