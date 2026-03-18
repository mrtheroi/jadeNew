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
        Schema::create('expense_types', function (Blueprint $table) {
            $table->id();

            // Nombre del gasto (ej. Luz, Renta, Compra de insumos…)
            $table->string('expense_type_name', 150);

            // Activo / Inactivo
            $table->boolean('is_active')->default(true);

            // Si en algún momento quieres auditar quién creó la categoría:
            // $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_types');
    }
};
