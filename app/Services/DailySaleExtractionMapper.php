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
        $comedor = $this->findArea($data['sales_by_area'] ?? [], 'COMEDOR');
        $payments = $this->indexPayments($data['payment_methods'] ?? $data['payment_summary'] ?? []);

        return [
            // Sales by area (COMEDOR)
            'alimentos' => (float) ($comedor['food_sales'] ?? 0),
            'bebidas' => (float) ($comedor['drink_sales'] ?? $comedor['beverage_sales'] ?? 0),
            'otros' => (float) ($comedor['other_sales'] ?? 0),
            'subtotal' => (float) ($comedor['subtotal'] ?? 0),
            'iva' => (float) ($comedor['tax'] ?? 0),
            'total' => (float) ($comedor['total'] ?? 0),
            'numero_personas' => (int) ($comedor['number_of_people'] ?? 0),
            'numero_cuentas' => (int) ($comedor['number_of_bills'] ?? $comedor['number_of_accounts'] ?? 0),
            'promedio_por_persona' => (float) ($comedor['average_per_person'] ?? 0),
            'cantidad_productos' => (int) ($comedor['product_count'] ?? 0),

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
     * Find an area by name in the sales_by_area array.
     *
     * @param  array<int, array<string, mixed>>  $areas
     * @return array<string, mixed>
     */
    private function findArea(array $areas, string $areaName): array
    {
        foreach ($areas as $area) {
            if (($area['area_name'] ?? '') === $areaName) {
                return $area;
            }
        }

        return [];
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
