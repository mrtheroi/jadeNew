<?php

namespace App\Livewire\Forms;

use App\Models\DailySale;
use Livewire\Form;

class ReconciliationForm extends Form
{
    public array $corte = [
        'efectivo_monto' => '',
        'efectivo_propina' => '',
        'debito_monto' => '',
        'debito_propina' => '',
        'credito_monto' => '',
        'credito_propina' => '',
        'credito_cliente_monto' => '',
        'credito_cliente_propina' => '',
    ];

    public string $reconciliationNotes = '';

    public function rules(): array
    {
        return [
            'corte.efectivo_monto' => 'nullable|numeric|min:0',
            'corte.efectivo_propina' => 'nullable|numeric|min:0',
            'corte.debito_monto' => 'nullable|numeric|min:0',
            'corte.debito_propina' => 'nullable|numeric|min:0',
            'corte.credito_monto' => 'nullable|numeric|min:0',
            'corte.credito_propina' => 'nullable|numeric|min:0',
            'corte.credito_cliente_monto' => 'nullable|numeric|min:0',
            'corte.credito_cliente_propina' => 'nullable|numeric|min:0',
            'reconciliationNotes' => 'nullable|string|max:1000',
        ];
    }

    public function fillFromSale(DailySale $sale): void
    {
        if ($sale->reconciliation_data) {
            $this->corte = array_merge($this->corte, $sale->reconciliation_data);
        } else {
            $this->corte = array_fill_keys(array_keys($this->corte), '');
        }

        $this->reconciliationNotes = $sale->reconciliation_notes ?? '';
    }

    /**
     * Convierte strings a floats, '' a null.
     */
    public function resolvedCorte(): array
    {
        return array_map(fn ($v) => $v === '' ? null : (float) $v, $this->corte);
    }

    /**
     * Calcula la diferencia total entre el corte y los datos del sistema.
     */
    public function calculateDifference(DailySale $sale): float
    {
        $corteData = $this->resolvedCorte();
        $fields = array_keys($this->corte);
        $totalDiff = 0;

        foreach ($fields as $field) {
            if ($corteData[$field] !== null) {
                $totalDiff += $corteData[$field] - (float) $sale->{$field};
            }
        }

        return $totalDiff;
    }

    /**
     * Determina el status de reconciliacion.
     */
    public function resolveStatus(DailySale $sale): string
    {
        return abs($this->calculateDifference($sale)) < 0.01
            ? 'reconciled'
            : 'discrepancy';
    }
}
