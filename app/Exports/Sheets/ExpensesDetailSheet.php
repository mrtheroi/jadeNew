<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

final class ExpensesDetailSheet implements FromArray, WithTitle
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Detalle';
    }

    public function array(): array
    {
        $meta = $this->data['meta'];
        $detail = $this->data['detail'];

        $rows = [];
        $rows[] = ['DETALLE DE GASTOS'];
        $rows[] = ['Unidad', $meta['business_unit'], 'Periodo', $meta['period_key'] ?? '—'];
        $rows[] = []; // blank

        $rows[] = [
            'Unidad',
            'Periodo',
            'Tipo de gasto',
            'Categoría',
            'Proveedor',
            'Fecha pago',
            'Mes',
            'Monto',
            'Tipo pago',
            'Estatus',
            'Observaciones',
            'Movimiento',
        ];

        foreach ($detail as $d) {
            $movement = ((float)$d['amount'] < 0) ? 'Ajuste' : 'Gasto';

            $rows[] = [
                $d['business_unit'],
                $d['month'],
                $d['expense_type'],
                $d['category'],
                $d['provider'],
                $d['payment_date'],
                $d['month'],
                (float)$d['amount'],
                $movement, // ✅ nuevo
                $d['payment_type'],
                $d['status'],
                $d['notes'],
            ];
        }

        return $rows;
    }
}
