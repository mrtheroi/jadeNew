<?php

namespace App\Services\Reports;

use App\Exports\ExpensesReportExport;
use App\Models\IncomePeriod;
use App\Models\Supply;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExpensesReportService
{
    /**
     * Construye el query con filtros (útil si aún quieres generar el reporte por filtros).
     * OJO: Si estás exportando desde UI, lo ideal es usar buildReportDataFromSupplyIds()
     * para que sea 1:1 con la tabla (misma consulta/filtros exactos).
     */
    private function buildSuppliesQuery(
        ?string $search,
        ?string $businessUnit,
        ?string $fromDate,
        ?string $toDate
    ): Builder {
        $q = Supply::query()
            ->with(['category.expenseType'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        // Filtro por unidad
        if ($businessUnit && trim($businessUnit) !== '') {
            $q->whereHas('category', fn (Builder $cq) => $cq->where('business_unit', $businessUnit));
        }

        // Rango fechas
        if ($fromDate) {
            $q->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $q->whereDate('payment_date', '<=', $toDate);
        }

        // Filtro por texto (alineado a tu render de supplies)
        if ($search && trim($search) !== '') {
            $s = trim($search);
            $q->where(function (Builder $w) use ($s) {
                $w->whereHas('category', function (Builder $cq) use ($s) {
                    $cq->where('expense_name', 'like', "%{$s}%")
                        ->orWhere('provider_name', 'like', "%{$s}%")
                        ->orWhereHas('expenseType', function (Builder $etq) use ($s) {
                            $etq->where('expense_type_name', 'like', "%{$s}%");
                        });
                })
                    ->orWhere('status', 'like', "%{$s}%")
                    ->orWhere('payment_type', 'like', "%{$s}%");
            });
        }

        return $q;
    }

    /**
     * Si no llega from/to pero llega periodKey, calculamos el rango del mes.
     */
    private function normalizeRange(?string $fromDate, ?string $toDate, ?string $periodKey): array
    {
        if ((!$fromDate || !$toDate) && $periodKey && preg_match('/^\d{4}-\d{2}$/', $periodKey)) {
            $fromDate = Carbon::createFromFormat('Y-m', $periodKey)->startOfMonth()->toDateString();
            $toDate   = Carbon::createFromFormat('Y-m', $periodKey)->endOfMonth()->toDateString();
        }

        return [$fromDate, $toDate];
    }

    /**
     * Armado del payload final del reporte (DRY).
     */
    private function buildPayload(
        Collection $supplies,
        string $businessUnit,
        ?string $fromDate,
        ?string $toDate,
        ?string $periodKey
    ): array {
        // Income (si lo manejas por period_key + unit)
        $income = null;
        if ($periodKey) {
            $income = IncomePeriod::query()
                ->where('business_unit', $businessUnit)
                ->where('period_key', $periodKey)
                ->first();
        }

        // Detalle (tabla)
        $detail = $supplies->map(function (Supply $s) {
            return [
                'business_unit'   => $s->category?->business_unit ?? '—',
                'expense_type'    => $s->category?->expenseType?->expense_type_name ?? '—',
                'category'        => $s->category?->expense_name ?? '—',
                'provider'        => $s->category?->provider_name ?? ($s->provider_name ?? '—'),
                'payment_date'    => optional($s->payment_date)->format('Y-m-d'),
                'month'           => optional($s->payment_date)->format('Y-m'),
                'amount'          => (float) $s->amount,
                'payment_type'    => $s->payment_type ?? '—',
                'status'          => $s->status ?? '—',
                'notes'           => $s->notes ?? '',
            ];
        });

        // Resumen: por tipo de gasto + categoría
        $summary = $supplies
            ->groupBy(function (Supply $s) {
                $type = $s->category?->expenseType?->expense_type_name ?? '—';
                $cat  = $s->category?->expense_name ?? '—';
                return $type . '|||'. $cat;
            })
            ->map(function (Collection $items) {
                /** @var Supply $first */
                $first = $items->first();

                $gross = (float) $items->where('amount', '>', 0)->sum('amount');  // positivos
                $adj   = (float) $items->where('amount', '<', 0)->sum('amount');  // negativos (ya viene negativo)
                $net   = (float) $items->sum('amount');                           // neto

                return [
                    'business_unit' => $first->category?->business_unit ?? '—',
                    'expense_type'  => $first->category?->expenseType?->expense_type_name ?? '—',
                    'category'      => $first->category?->expense_name ?? '—',
                    'gross'         => $gross,
                    'adjustments'   => $adj,
                    'net'           => $net,
                ];
            })
            ->values()
            ->sortBy(['expense_type', 'category'])
            ->values();

        // Totales generales del reporte
        $totalGross = (float) $supplies->where('amount', '>', 0)->sum('amount');
        $totalAdj   = (float) $supplies->where('amount', '<', 0)->sum('amount'); // negativo
        $totalNet   = (float) $supplies->sum('amount');

        $incomeAmount = $income ? (float) $income->income_amount : null;

        return [
            'meta' => [
                'business_unit'   => $businessUnit,
                'from_date'       => $fromDate,
                'to_date'         => $toDate,
                'period_key'      => $periodKey,
                'income_amount'   => $incomeAmount,
                'generated_at'    => now()->format('Y-m-d H:i'),
            ],
            'summary' => $summary,
            'detail'  => $detail,
            'totals'  => [
                'gross_total'    => $totalGross,
                'adjust_total'   => $totalAdj,
                'net_total'      => $totalNet,
                'income_amount'  => $incomeAmount,
                'pct_income'     => ($incomeAmount && $incomeAmount > 0) ? ($totalNet / $incomeAmount) : null,
            ],
        ];
    }

    /**
     * Data normalizada para reporte (modo "por filtros").
     * Mantengo este método para compatibilidad.
     */
    public function buildReportData(
        ?string $search,
        string $businessUnit,
        ?string $fromDate,
        ?string $toDate,
        ?string $periodKey = null, // YYYY-MM
    ): array {
        [$fromDate, $toDate] = $this->normalizeRange($fromDate, $toDate, $periodKey);

        $supplies = $this->buildSuppliesQuery($search, $businessUnit, $fromDate, $toDate)->get();

        return $this->buildPayload($supplies, $businessUnit, $fromDate, $toDate, $periodKey);
    }

    /**
     * ✅ Nuevo: Construye el reporte desde una lista de IDs exacta.
     * Esto permite que Excel/PDF sea 1:1 con la tabla (misma baseQuery que UI).
     */
    public function buildReportDataFromSupplyIds(
        array $supplyIds,
        string $businessUnit,
        ?string $fromDate,
        ?string $toDate,
        ?string $periodKey = null
    ): array {
        [$fromDate, $toDate] = $this->normalizeRange($fromDate, $toDate, $periodKey);

        if (empty($supplyIds)) {
            return $this->buildPayload(collect(), $businessUnit, $fromDate, $toDate, $periodKey);
        }

        $supplies = Supply::query()
            ->with(['category.expenseType'])
            ->whereIn('id', $supplyIds)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        return $this->buildPayload($supplies, $businessUnit, $fromDate, $toDate, $periodKey);
    }

    /**
     * Descargar Excel (2 hojas: Resumen + Detalle)
     */
    public function downloadExcel(array $reportData, string $fileName): BinaryFileResponse
    {
        return Excel::download(
            new ExpensesReportExport($reportData),
            $fileName
        );
    }

    /**
     * Descargar PDF (resumen)
     */
    public function downloadPdf(array $reportData, string $fileName)
    {
        $pdf = Pdf::loadView('reports.expenses-pdf', $reportData)
            ->setPaper('letter', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }
}
