<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();

            // Relación con categorías (unidad de negocio, tipo de gasto, proveedor)
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            // Monto del gasto
            $table->decimal('amount', 12, 2);

            // Tipo de pago (efectivo, transferencia, tarjeta_credito, etc.)
            $table->string('payment_type', 50)->nullable();

            // Fecha de pago (la usaremos para mes y vencimiento)
            $table->date('payment_date')->nullable();

            // Opcional: almacenamiento textual del mes/año (por si luego quieres reportes directos)
            $table->string('payment_month', 30)->nullable();

            // Estatus del pago: pendiente, pagado, cancelado
            $table->string('status', 20)->default('pendiente');

            // Observaciones
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
