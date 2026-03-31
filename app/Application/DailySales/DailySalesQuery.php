<?php

namespace App\Application\DailySales;

use App\Application\Helpers\PeriodRange;
use App\Models\DailySale;
use Illuminate\Database\Eloquent\Builder;

class DailySalesQuery
{
    /**
     * Base query para la tabla de daily_sales.
     *
     * Filters esperados:
     * - search (string|null)
     * - business_unit (string|null)
     * - period_key (string|null)
     *
     * @return array{0: Builder, 1: string, 2: string, 3: string}
     */
    public function base(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $businessUnit = $filters['business_unit'] ?? null;
        $periodKey = $filters['period_key'] ?? null;

        [$from, $to, $periodKey] = PeriodRange::fromKey($periodKey);

        $q = DailySale::query()
            ->inPeriod($from, $to)
            ->byUnit($businessUnit);

        if ($search !== '') {
            $q->where('business_unit', 'like', "%{$search}%");
        }

        return [$q, $from, $to, $periodKey];
    }

    /**
     * Totales de ventas completadas para las summary cards.
     */
    public function totals(Builder $query): object
    {
        return (clone $query)
            ->completed()
            ->selectRaw('
                SUM(alimentos) as total_alimentos,
                SUM(bebidas) as total_bebidas,
                SUM(otros) as total_otros,
                SUM(subtotal) as total_subtotal,
                SUM(iva) as total_iva,
                SUM(total) as total_total
            ')
            ->first();
    }
}
