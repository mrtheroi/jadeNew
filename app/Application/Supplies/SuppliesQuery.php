<?php

namespace App\Application\Supplies;

use App\Models\Category;
use App\Models\IncomePeriod;
use App\Models\Supply;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SuppliesQuery
{
    /**
     * Base query para la tabla de supplies.
     *
     * Filters esperados:
     * - search (string|null)
     * - business_unit (string|null)
     * - date_from (Y-m-d|null)
     * - date_to (Y-m-d|null)
     */
    public function base(array $filters): Builder
    {
        $search       = trim((string)($filters['search'] ?? ''));
        $businessUnit = $filters['business_unit'] ?? null;
        $from         = $filters['date_from'] ?? null;
        $to           = $filters['date_to'] ?? null;

        $q = Supply::query()
            ->with(['category.expenseType'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if ($businessUnit) {
            $q->whereHas('category', fn (Builder $cq) => $cq->where('business_unit', $businessUnit));
        }

        if ($from) {
            $q->whereDate('payment_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('payment_date', '<=', $to);
        }

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->whereHas('category', function (Builder $cq) use ($search) {
                    $cq->where('expense_name', 'like', "%{$search}%")
                        ->orWhere('provider_name', 'like', "%{$search}%")
                        ->orWhereHas('expenseType', function (Builder $etq) use ($search) {
                            $etq->where('expense_type_name', 'like', "%{$search}%");
                        });
                })
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('payment_type', 'like', "%{$search}%");
            });
        }

        return $q;
    }

    /**
     * Totales por unidad (cards/resumen).
     * Si viene business_unit, normalmente regresará 1 fila (esa unidad).
     */
    public function totalsByUnit(array $filters): Collection
    {
        $businessUnit = $filters['business_unit'] ?? null;
        $from         = $filters['date_from'] ?? null;
        $to           = $filters['date_to'] ?? null;

        $q = Supply::query()
            ->join('categories', 'supplies.category_id', '=', 'categories.id');

        if ($businessUnit) {
            $q->where('categories.business_unit', $businessUnit);
        }

        if ($from) {
            $q->whereDate('supplies.payment_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('supplies.payment_date', '<=', $to);
        }

        return $q->selectRaw('categories.business_unit as business_unit, SUM(supplies.amount) as total_amount')
            ->groupBy('categories.business_unit')
            ->get();
    }

    public function incomePeriod(string $businessUnit, string $periodKey): ?IncomePeriod
    {
        return IncomePeriod::query()
            ->where('business_unit', $businessUnit)
            ->where('period_key', $periodKey)
            ->first();
    }

    public function categories(): Collection
    {
        return Category::with('expenseType')
            ->orderBy('business_unit')
            ->orderBy('expense_name')
            ->orderBy('provider_name')
            ->get();
    }
}
