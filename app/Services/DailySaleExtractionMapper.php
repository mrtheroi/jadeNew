<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;

class DailySaleExtractionMapper
{
    /**
     * Map LlamaIndex extraction result JSON to daily_sales fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function map(array $data): array
    {
        $areas = $this->sumAreas($data['sales_by_area'] ?? []);
        $payments = $this->indexPayments($data['payment_methods'] ?? $data['payment_summary'] ?? []);

        return [
            // Sales (sum of all areas: COMEDOR + PLANTA ALTA, etc.)
            'alimentos' => $areas['food_sales'],
            'bebidas' => $areas['beverage_sales'],
            'otros' => $areas['other_sales'],
            'subtotal' => $areas['subtotal'],
            'iva' => $areas['tax'],
            'total' => $areas['total'],
            'numero_personas' => $areas['number_of_people'],
            'numero_cuentas' => $areas['number_of_accounts'],
            'promedio_por_persona' => $areas['average_per_person'],
            'cantidad_productos' => $areas['product_count'],

            // Payment summary
            'efectivo_monto' => (float) ($payments['EFECTIVO']['amount'] ?? 0),
            'efectivo_propina' => (float) ($payments['EFECTIVO']['tip'] ?? 0),
            'debito_monto' => (float) ($payments['TARJETA DEBITO']['amount'] ?? 0),
            'debito_propina' => (float) ($payments['TARJETA DEBITO']['tip'] ?? 0),
            'credito_monto' => (float) ($payments['TARJETA CREDITO']['amount'] ?? 0),
            'credito_propina' => (float) ($payments['TARJETA CREDITO']['tip'] ?? 0),
            'credito_cliente_monto' => (float) ($payments['CREDITO']['amount'] ?? 0),
            'credito_cliente_propina' => (float) ($payments['CREDITO']['tip'] ?? 0),

            // Report period — supports both v1 and v2 formats
            'period_start' => $this->parseDateTime(
                $data['report_details']['report_period_start'] ?? $data['report_period']['start_datetime'] ?? null
            ),
            'period_end' => $this->parseDateTime(
                $data['report_details']['report_period_end'] ?? $data['report_period']['end_datetime'] ?? null
            ),
        ];
    }

    /**
     * Sum numeric fields across all areas (COMEDOR, PLANTA ALTA, etc.).
     *
     * @param  array<int, array<string, mixed>>  $areas
     * @return array<string, float|int>
     */
    private function sumAreas(array $areas): array
    {
        $totals = [
            'food_sales' => 0.0,
            'beverage_sales' => 0.0,
            'other_sales' => 0.0,
            'subtotal' => 0.0,
            'tax' => 0.0,
            'total' => 0.0,
            'number_of_people' => 0,
            'number_of_accounts' => 0,
            'average_per_person' => 0.0,
            'product_count' => 0,
        ];

        foreach ($areas as $area) {
            $totals['food_sales'] += (float) ($area['food_sales'] ?? 0);
            $totals['beverage_sales'] += (float) ($area['drink_sales'] ?? $area['beverage_sales'] ?? 0);
            $totals['other_sales'] += (float) ($area['other_sales'] ?? 0);
            $totals['subtotal'] += (float) ($area['subtotal'] ?? 0);
            $totals['tax'] += (float) ($area['tax'] ?? 0);
            $totals['total'] += (float) ($area['total'] ?? 0);
            $totals['number_of_people'] += (int) ($area['number_of_people'] ?? 0);
            $totals['number_of_accounts'] += (int) ($area['number_of_bills'] ?? $area['number_of_accounts'] ?? 0);
            $totals['product_count'] += (int) ($area['product_count'] ?? 0);
        }

        if ($totals['number_of_people'] > 0) {
            $totals['average_per_person'] = round($totals['total'] / $totals['number_of_people'], 2);
        }

        return $totals;
    }

    /**
     * Index payment summary by method name.
     *
     * @param  array<int, array<string, mixed>>  $payments
     * @return array<string, array<string, mixed>>
     */
    private function indexPayments(array $payments): array
    {
        $indexed = [];

        foreach ($payments as $payment) {
            $method = $payment['method'] ?? $payment['payment_method'] ?? '';
            $indexed[$method] = $payment;
        }

        return $indexed;
    }

    /**
     * Parse datetime from various formats.
     */
    private function parseDateTime(?string $datetime): ?Carbon
    {
        if ($datetime === null || $datetime === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y h:i:s A', $datetime);
        } catch (\Exception) {
            try {
                return Carbon::parse($datetime);
            } catch (\Exception) {
                return null;
            }
        }
    }
}
