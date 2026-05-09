<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tablas con columna business_unit que almacenan el nombre legible de la unidad.
     */
    private const TABLES = [
        'categories',
        'income_periods',
        'employees',
        'purchase_orders',
        'daily_sales',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (self::TABLES as $table) {
                DB::table($table)
                    ->where('business_unit', 'Fuego Ambar')
                    ->update(['business_unit' => 'Jade Orgánico']);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            foreach (self::TABLES as $table) {
                DB::table($table)
                    ->where('business_unit', 'Jade Orgánico')
                    ->update(['business_unit' => 'Fuego Ambar']);
            }
        });
    }
};
