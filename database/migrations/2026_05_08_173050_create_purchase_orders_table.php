<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // Identificación de la OC
            $table->string('oc_number', 50)->unique();
            $table->date('oc_date');
            $table->string('business_unit', 50);

            // Totales denormalizados (se calculan al generar la OC)
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->unsignedInteger('total_items')->default(0);

            // Metadatos
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('closed');

            // Auditoría
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            // Índices para filtros y reportes
            $table->index('business_unit');
            $table->index('oc_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
