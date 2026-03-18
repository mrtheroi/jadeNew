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
        Schema::table('daily_sales', function (Blueprint $table) {
            // Turno y estado
            $table->unsignedTinyInteger('turno')->default(1)->after('operation_date');
            $table->string('status', 30)->default('completed')->after('total');
            $table->text('error_message')->nullable()->after('status');

            // LlamaIndex
            $table->string('llama_job_id')->nullable()->after('error_message');
            $table->json('extraction_raw_json')->nullable()->after('llama_job_id');

            // Payment summary - montos
            $table->decimal('efectivo_monto', 12, 2)->default(0)->after('extraction_raw_json');
            $table->decimal('efectivo_propina', 12, 2)->default(0)->after('efectivo_monto');
            $table->decimal('debito_monto', 12, 2)->default(0)->after('efectivo_propina');
            $table->decimal('debito_propina', 12, 2)->default(0)->after('debito_monto');
            $table->decimal('credito_monto', 12, 2)->default(0)->after('debito_propina');
            $table->decimal('credito_propina', 12, 2)->default(0)->after('credito_monto');
            $table->decimal('credito_cliente_monto', 12, 2)->default(0)->after('credito_propina');
            $table->decimal('credito_cliente_propina', 12, 2)->default(0)->after('credito_cliente_monto');

            // Sales by area (COMEDOR)
            $table->unsignedInteger('numero_personas')->default(0)->after('credito_cliente_propina');
            $table->unsignedInteger('numero_cuentas')->default(0)->after('numero_personas');
            $table->decimal('promedio_por_persona', 12, 2)->default(0)->after('numero_cuentas');
            $table->unsignedInteger('cantidad_productos')->default(0)->after('promedio_por_persona');

            // Report period
            $table->dateTime('period_start')->nullable()->after('cantidad_productos');
            $table->dateTime('period_end')->nullable()->after('period_start');

            // Indices
            $table->index('llama_job_id');
            $table->index('status');
        });

        // Cambiar constraint unico
        Schema::table('daily_sales', function (Blueprint $table) {
            $table->dropUnique(['business_unit', 'operation_date']);
            $table->unique(['business_unit', 'operation_date', 'turno']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_sales', function (Blueprint $table) {
            $table->dropUnique(['business_unit', 'operation_date', 'turno']);
            $table->unique(['business_unit', 'operation_date']);
        });

        Schema::table('daily_sales', function (Blueprint $table) {
            $table->dropIndex(['llama_job_id']);
            $table->dropIndex(['status']);

            $table->dropColumn([
                'turno', 'status', 'error_message',
                'llama_job_id', 'extraction_raw_json',
                'efectivo_monto', 'efectivo_propina',
                'debito_monto', 'debito_propina',
                'credito_monto', 'credito_propina',
                'credito_cliente_monto', 'credito_cliente_propina',
                'numero_personas', 'numero_cuentas',
                'promedio_por_persona', 'cantidad_productos',
                'period_start', 'period_end',
            ]);
        });
    }
};
