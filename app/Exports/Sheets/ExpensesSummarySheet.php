<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExpensesSummarySheet implements FromArray, WithColumnFormatting
{
    /**
     * La tabla empieza después del bloque de encabezado.
     * 1: Título
     * 2: (vacío)
     * 3: Unidad / Generado
     * 4: Periodo
     * 5: Ingreso
     * 6: (vacío)
     * 7: Encabezados de tabla
     * 8+: Filas
     */
    private int $tableHeaderRow = 7;

    public function __construct(
        private array $report
    ) {}

    public function array(): array
    {
        $summary = $this->report['summary'] ?? [];
        $meta    = $this->report['meta'] ?? [];
        $totals  = $this->report['totals'] ?? [];

        $businessUnit = (string)($meta['business_unit'] ?? '—');
        $periodKey    = (string)($meta['period_key'] ?? '—');
        $generatedAt  = (string)($meta['generated_at'] ?? now()->format('Y-m-d H:i'));
        $income       = (float)($meta['income_amount'] ?? 0);

        $rows = [];

        // ✅ Encabezado tipo Excel
        $rows[] = ['REPORTE DE GASTOS'];
        $rows[] = [];
        $rows[] = ['Unidad', $businessUnit, 'Generado', $generatedAt];
        $rows[] = ['Periodo', $periodKey];
        $rows[] = ['Ingreso bancario', $income];
        $rows[] = [];

        // ✅ Encabezados de la tabla (manuales)
        $rows[] = [
            'Tipo de gasto',
            'Categoría',
            'Gasto',
            'Ajustes',
            'Neto',
            '% sobre ingreso',
        ];

        // ✅ Filas del resumen
        foreach ($summary as $item) {
            $net = (float)($item['net'] ?? 0);
            $pct = ($income > 0) ? ($net / $income) : 0;

            $rows[] = [
                $item['expense_type'] ?? '—',
                $item['category'] ?? '—',
                (float)($item['gross'] ?? 0),
                (float)($item['adjustments'] ?? 0),
                (float)($item['net'] ?? 0),
                $pct, // decimal, se verá como % por formato
            ];
        }

        // ✅ Totales al final
        $netTotal = (float)($totals['net_total'] ?? 0);
        $rows[] = [
            'TOTALES',
            '',
            (float)($totals['gross_total'] ?? 0),
            (float)($totals['adjust_total'] ?? 0),
            (float)($totals['net_total'] ?? 0),
            ($income > 0) ? ($netTotal / $income) : 0,
        ];

        return $rows;
    }

    public function columnFormats(): array
    {
        // Como el encabezado ocupa filas, aplicamos formato a columnas completas
        // (Excel lo aplica a toda la columna; las filas de texto no se ven afectadas)
        return [
            'C' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'D' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'E' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'F' => NumberFormat::FORMAT_PERCENTAGE_00, // ✅ porcentaje con 2 decimales
        ];
    }
}
