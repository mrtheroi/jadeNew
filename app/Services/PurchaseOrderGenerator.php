<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supply;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderGenerator
{
    /**
     * Genera una OC postmortem para todas las compras del día y unidad
     * que aún no estén asociadas a una OC.
     *
     * @throws RuntimeException si no hay compras elegibles para agrupar.
     */
    public function generate(string $businessUnit, CarbonInterface $date, int $userId): PurchaseOrder
    {
        $eligible = $this->eligibleSupplies($businessUnit, $date);

        if ($eligible->isEmpty()) {
            throw new RuntimeException('No hay compras elegibles para generar una OC con esos parámetros.');
        }

        return DB::transaction(function () use ($eligible, $businessUnit, $date, $userId) {
            $oc = PurchaseOrder::create([
                'oc_number' => $this->buildOcNumber($businessUnit, $date),
                'oc_date' => $date->toDateString(),
                'business_unit' => $businessUnit,
                'total_amount' => (float) $eligible->sum('amount'),
                'total_items' => $eligible->count(),
                'status' => PurchaseOrder::STATUS_CLOSED,
                'created_by' => $userId,
                'closed_at' => now(),
            ]);

            Supply::query()
                ->whereIn('id', $eligible->pluck('id')->all())
                ->update(['purchase_order_id' => $oc->id]);

            return $oc->fresh();
        });
    }

    /**
     * Cancela una OC y libera todas las compras asociadas (purchase_order_id = null).
     */
    public function cancel(PurchaseOrder $oc): void
    {
        if ($oc->isCancelled()) {
            return;
        }

        DB::transaction(function () use ($oc) {
            Supply::query()
                ->where('purchase_order_id', $oc->id)
                ->update(['purchase_order_id' => null]);

            $oc->update([
                'status' => PurchaseOrder::STATUS_CANCELLED,
            ]);
        });
    }

    /**
     * Compras del día y unidad que están sin OC asignada.
     *
     * @return Collection<int, Supply>
     */
    public function eligibleSupplies(string $businessUnit, CarbonInterface $date): Collection
    {
        return Supply::query()
            ->whereNull('purchase_order_id')
            ->whereDate('payment_date', $date->toDateString())
            ->whereHas('category', fn ($q) => $q->where('business_unit', $businessUnit))
            ->with(['category'])
            ->get();
    }

    /**
     * Construye un número de OC único para la combinación unidad+fecha.
     * Si ya existen OCs para ese día y unidad, agrega sufijo -1, -2, etc.
     */
    private function buildOcNumber(string $businessUnit, CarbonInterface $date): string
    {
        $unitSlug = str_replace(' ', '', $businessUnit);
        $base = sprintf('OC-%s-%s', $unitSlug, $date->format('Y-m-d'));

        $existing = PurchaseOrder::query()
            ->where('oc_number', 'like', $base.'%')
            ->count();

        return $existing === 0 ? $base : sprintf('%s-%d', $base, $existing + 1);
    }
}
