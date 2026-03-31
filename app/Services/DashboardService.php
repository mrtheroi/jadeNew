<?php

namespace App\Services;

use App\Application\Helpers\PeriodRange;
use App\Models\DailySale;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Returns all data needed for both the dashboard view and exports.
     *
     * @return array{
     *     totalSales: float,
     *     totalSubtotal: float,
     *     totalIva: float,
     *     totalAlimentos: float,
     *     totalBebidas: float,
     *     totalOtros: float,
     *     totalExpenses: float,
     *     profit: float,
     *     profitPercent: float,
     *     efectivoMonto: float,
     *     efectivoPropina: float,
     *     debitoMonto: float,
     *     debitoPropina: float,
     *     creditoMonto: float,
     *     creditoPropina: float,
     *     creditoClienteMonto: float,
     *     creditoClientePropina: float,
     *     totalPropinas: float,
     *     totalPersonas: int,
     *     totalCuentas: int,
     *     ticketPromedio: float,
     *     turno1Ventas: float,
     *     turno1Personas: int,
     *     turno1Ticket: float,
     *     turno2Ventas: float,
     *     turno2Personas: int,
     *     turno2Ticket: float,
     *     labelsUnits: array,
     *     dataUnits: array,
     *     paymentLabels: array,
     *     paymentData: array,
     *     expenseGroups: array,
     *     periodKey: string,
     *     from: string,
     *     to: string,
     *     businessUnit: string,
     * }
     */
    public function getFullDashboardData(?string $businessUnit, ?string $periodKey): array
    {
        [$from, $to, $periodKey] = PeriodRange::fromKey($periodKey);

        $salesTotals = $this->getSalesTotals($from, $to, $businessUnit);
        $totalSales = (float) ($salesTotals->total_sales ?? 0);
        $totalExpenses = $this->getExpensesTotals($from, $to, $businessUnit);
        $profit = $totalSales - $totalExpenses;

        $efectivoMonto = (float) ($salesTotals->total_efectivo_monto ?? 0);
        $debitoMonto = (float) ($salesTotals->total_debito_monto ?? 0);
        $creditoMonto = (float) ($salesTotals->total_credito_monto ?? 0);
        $creditoClienteMonto = (float) ($salesTotals->total_credito_cliente_monto ?? 0);

        $efectivoPropina = (float) ($salesTotals->total_efectivo_propina ?? 0);
        $debitoPropina = (float) ($salesTotals->total_debito_propina ?? 0);
        $creditoPropina = (float) ($salesTotals->total_credito_propina ?? 0);
        $creditoClientePropina = (float) ($salesTotals->total_credito_cliente_propina ?? 0);

        $totalPersonas = (int) ($salesTotals->total_personas ?? 0);

        $turnoBreakdown = $this->getTurnoBreakdown($from, $to, $businessUnit);
        $salesByUnit = $this->getSalesByUnit($from, $to, $businessUnit);
        $paymentMethodTotals = $this->getPaymentMethodTotals($efectivoMonto, $debitoMonto, $creditoMonto, $creditoClienteMonto);
        $expenseGroups = $this->getExpenseGroups($from, $to, $businessUnit, $totalSales);

        return [
            'periodKey' => $periodKey,
            'from' => $from,
            'to' => $to,
            'businessUnit' => $businessUnit ?: 'Todas',
            'totalSales' => $totalSales,
            'totalSubtotal' => (float) ($salesTotals->total_subtotal ?? 0),
            'totalIva' => (float) ($salesTotals->total_iva ?? 0),
            'totalAlimentos' => (float) ($salesTotals->total_alimentos ?? 0),
            'totalBebidas' => (float) ($salesTotals->total_bebidas ?? 0),
            'totalOtros' => (float) ($salesTotals->total_otros ?? 0),
            'totalExpenses' => $totalExpenses,
            'profit' => $profit,
            'profitPercent' => $totalSales > 0 ? round(($profit / $totalSales) * 100, 2) : 0,
            'efectivoMonto' => $efectivoMonto,
            'efectivoPropina' => $efectivoPropina,
            'debitoMonto' => $debitoMonto,
            'debitoPropina' => $debitoPropina,
            'creditoMonto' => $creditoMonto,
            'creditoPropina' => $creditoPropina,
            'creditoClienteMonto' => $creditoClienteMonto,
            'creditoClientePropina' => $creditoClientePropina,
            'totalPropinas' => $efectivoPropina + $debitoPropina + $creditoPropina + $creditoClientePropina,
            'totalPersonas' => $totalPersonas,
            'totalCuentas' => (int) ($salesTotals->total_cuentas ?? 0),
            'ticketPromedio' => $totalPersonas > 0 ? round($totalSales / $totalPersonas, 2) : 0,
            'turno1Ventas' => $turnoBreakdown['turno1Ventas'],
            'turno1Personas' => $turnoBreakdown['turno1Personas'],
            'turno1Ticket' => $turnoBreakdown['turno1Ticket'],
            'turno2Ventas' => $turnoBreakdown['turno2Ventas'],
            'turno2Personas' => $turnoBreakdown['turno2Personas'],
            'turno2Ticket' => $turnoBreakdown['turno2Ticket'],
            'labelsUnits' => $salesByUnit['labels'],
            'dataUnits' => $salesByUnit['data'],
            'paymentLabels' => $paymentMethodTotals['labels'],
            'paymentData' => $paymentMethodTotals['data'],
            'expenseGroups' => $expenseGroups,
            'grandTotalExpenses' => $totalExpenses,
        ];
    }

    private function getSalesTotals(string $from, string $to, ?string $unit): object
    {
        return DailySale::query()
            ->completed()
            ->inPeriod($from, $to)
            ->byUnit($unit)
            ->selectRaw('
                COALESCE(SUM(total), 0) as total_sales,
                COALESCE(SUM(subtotal), 0) as total_subtotal,
                COALESCE(SUM(iva), 0) as total_iva,
                COALESCE(SUM(alimentos), 0) as total_alimentos,
                COALESCE(SUM(bebidas), 0) as total_bebidas,
                COALESCE(SUM(otros), 0) as total_otros,
                COALESCE(SUM(efectivo_monto), 0) as total_efectivo_monto,
                COALESCE(SUM(efectivo_propina), 0) as total_efectivo_propina,
                COALESCE(SUM(debito_monto), 0) as total_debito_monto,
                COALESCE(SUM(debito_propina), 0) as total_debito_propina,
                COALESCE(SUM(credito_monto), 0) as total_credito_monto,
                COALESCE(SUM(credito_propina), 0) as total_credito_propina,
                COALESCE(SUM(credito_cliente_monto), 0) as total_credito_cliente_monto,
                COALESCE(SUM(credito_cliente_propina), 0) as total_credito_cliente_propina,
                COALESCE(SUM(numero_personas), 0) as total_personas,
                COALESCE(SUM(numero_cuentas), 0) as total_cuentas
            ')
            ->first();
    }

    private function getExpensesTotals(string $from, string $to, ?string $unit): float
    {
        return (float) Supply::query()
            ->join('categories', 'supplies.category_id', '=', 'categories.id')
            ->whereBetween('supplies.payment_date', [$from, $to])
            ->where('supplies.status', '!=', 'cancelado')
            ->when($unit, fn ($q) => $q->where('categories.business_unit', $unit))
            ->selectRaw('COALESCE(SUM(ABS(supplies.amount)), 0) as total')
            ->value('total');
    }

    /**
     * @return array{turno1Ventas: float, turno1Personas: int, turno1Ticket: float, turno2Ventas: float, turno2Personas: int, turno2Ticket: float}
     */
    private function getTurnoBreakdown(string $from, string $to, ?string $unit): array
    {
        $turnoData = DailySale::query()
            ->completed()
            ->inPeriod($from, $to)
            ->byUnit($unit)
            ->select('turno', DB::raw('
                COALESCE(SUM(total), 0) as total_ventas,
                COALESCE(SUM(numero_personas), 0) as total_personas
            '))
            ->groupBy('turno')
            ->get()
            ->keyBy('turno');

        $turno1Ventas = (float) ($turnoData[1]->total_ventas ?? 0);
        $turno1Personas = (int) ($turnoData[1]->total_personas ?? 0);
        $turno2Ventas = (float) ($turnoData[2]->total_ventas ?? 0);
        $turno2Personas = (int) ($turnoData[2]->total_personas ?? 0);

        return [
            'turno1Ventas' => $turno1Ventas,
            'turno1Personas' => $turno1Personas,
            'turno1Ticket' => $turno1Personas > 0 ? round($turno1Ventas / $turno1Personas, 2) : 0,
            'turno2Ventas' => $turno2Ventas,
            'turno2Personas' => $turno2Personas,
            'turno2Ticket' => $turno2Personas > 0 ? round($turno2Ventas / $turno2Personas, 2) : 0,
        ];
    }

    /**
     * @return array{labels: array, data: array}
     */
    private function getSalesByUnit(string $from, string $to, ?string $unit): array
    {
        $byUnit = DailySale::query()
            ->completed()
            ->inPeriod($from, $to)
            ->byUnit($unit)
            ->select('business_unit', DB::raw('COALESCE(SUM(total), 0) as total_amount'))
            ->groupBy('business_unit')
            ->orderBy('business_unit')
            ->get();

        return [
            'labels' => $byUnit->pluck('business_unit')->values()->toArray(),
            'data' => $byUnit->pluck('total_amount')->map(fn ($v) => (float) $v)->values()->toArray(),
        ];
    }

    /**
     * @return array{labels: array, data: array}
     */
    private function getPaymentMethodTotals(float $efectivo, float $debito, float $credito, float $creditoCliente): array
    {
        return [
            'labels' => ['Efectivo', 'T. Debito', 'T. Credito', 'Credito'],
            'data' => [$efectivo, $debito, $credito, $creditoCliente],
        ];
    }

    /**
     * @return array<int, array{expense_type: string, categories: array, total: float, percent: float}>
     */
    private function getExpenseGroups(string $from, string $to, ?string $unit, float $totalSales): array
    {
        $rows = Supply::query()
            ->join('categories', 'supplies.category_id', '=', 'categories.id')
            ->join('expense_types', 'categories.expense_type_id', '=', 'expense_types.id')
            ->where('expense_types.is_active', true)
            ->whereBetween('supplies.payment_date', [$from, $to])
            ->where('supplies.status', '!=', 'cancelado')
            ->when($unit, fn ($q) => $q->where('categories.business_unit', $unit))
            ->selectRaw('
                expense_types.expense_type_name,
                categories.expense_name,
                COALESCE(SUM(ABS(supplies.amount)), 0) as total_amount
            ')
            ->groupBy('expense_types.expense_type_name', 'categories.expense_name')
            ->orderBy('expense_types.expense_type_name')
            ->orderBy('categories.expense_name')
            ->get();

        $grouped = $rows->groupBy('expense_type_name');

        $groups = [];

        foreach ($grouped as $typeName => $categoryRows) {
            $groupTotal = $categoryRows->sum('total_amount');

            $categories = $categoryRows->map(fn ($row) => [
                'name' => $row->expense_name,
                'amount' => (float) $row->total_amount,
                'percent' => $totalSales > 0 ? round(((float) $row->total_amount / $totalSales) * 100, 2) : 0,
            ])->toArray();

            $groups[] = [
                'expense_type' => $typeName,
                'categories' => $categories,
                'total' => (float) $groupTotal,
                'percent' => $totalSales > 0 ? round(($groupTotal / $totalSales) * 100, 2) : 0,
            ];
        }

        return $groups;
    }
}
