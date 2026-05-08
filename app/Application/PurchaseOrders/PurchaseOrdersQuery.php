<?php

namespace App\Application\PurchaseOrders;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PurchaseOrdersQuery
{
    /**
     * Listado paginable de OCs.
     *
     * Filters esperados:
     * - search (string|null) — busca en oc_number y notes
     * - business_unit (string|null)
     * - status (closed|cancelled|all|null) — default 'all'
     * - date_from (Y-m-d|null)
     * - date_to (Y-m-d|null)
     */
    public function base(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $businessUnit = $filters['business_unit'] ?? null;
        $status = $filters['status'] ?? null;
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;

        $q = PurchaseOrder::query()
            ->with(['creator'])
            ->orderByDesc('oc_date')
            ->orderByDesc('id');

        if ($businessUnit) {
            $q->where('business_unit', $businessUnit);
        }

        if ($status === PurchaseOrder::STATUS_CLOSED || $status === PurchaseOrder::STATUS_CANCELLED) {
            $q->where('status', $status);
        }

        if ($from) {
            $q->whereDate('oc_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('oc_date', '<=', $to);
        }

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->where('oc_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $q;
    }

    /**
     * Total monto sumado de OCs cerradas que matchean los filtros.
     */
    public function totalAmount(array $filters): float
    {
        return (float) $this->countBase($filters)
            ->where('status', PurchaseOrder::STATUS_CLOSED)
            ->sum('total_amount');
    }

    /**
     * Cantidad de OCs cerradas que matchean los filtros.
     */
    public function totalClosed(array $filters): int
    {
        return (int) $this->countBase($filters)
            ->where('status', PurchaseOrder::STATUS_CLOSED)
            ->count();
    }

    /**
     * Cantidad de OCs anuladas que matchean los filtros.
     */
    public function totalCancelled(array $filters): int
    {
        return (int) $this->countBase($filters)
            ->where('status', PurchaseOrder::STATUS_CANCELLED)
            ->count();
    }

    /**
     * Breakdown por unidad: cuenta de OCs cerradas y monto total por business_unit.
     *
     * @return Collection<int, object{business_unit: string, count: int, total_amount: float}>
     */
    public function totalsByUnit(array $filters): Collection
    {
        return $this->countBase($filters)
            ->where('status', PurchaseOrder::STATUS_CLOSED)
            ->selectRaw('business_unit, COUNT(*) as count, SUM(total_amount) as total_amount')
            ->groupBy('business_unit')
            ->orderByDesc('total_amount')
            ->get();
    }

    /**
     * Query base para conteos: aplica todos los filtros menos status.
     */
    private function countBase(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $businessUnit = $filters['business_unit'] ?? null;
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;

        $q = PurchaseOrder::query();

        if ($businessUnit) {
            $q->where('business_unit', $businessUnit);
        }

        if ($from) {
            $q->whereDate('oc_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('oc_date', '<=', $to);
        }

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->where('oc_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $q;
    }
}
