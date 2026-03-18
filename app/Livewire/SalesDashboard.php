<?php

namespace App\Livewire;

use App\Models\DailySale;
use App\Models\ExpenseType;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class SalesDashboard extends Component
{
    #[Url]
    public ?string $business_unit = null;

    #[Url]
    #[Rule('nullable|regex:/^\d{4}-\d{2}$/')]
    public ?string $period_key = null;

    // Sales cards
    public float $totalSales = 0.0;

    public float $totalSubtotal = 0.0;

    public float $totalIva = 0.0;

    public float $totalAlimentos = 0.0;

    public float $totalBebidas = 0.0;

    public float $totalOtros = 0.0;

    // Expenses card
    public float $totalExpenses = 0.0;

    // Profit
    public float $profit = 0.0;

    // Payment methods
    public float $efectivoMonto = 0.0;

    public float $efectivoPropina = 0.0;

    public float $debitoMonto = 0.0;

    public float $debitoPropina = 0.0;

    public float $creditoMonto = 0.0;

    public float $creditoPropina = 0.0;

    public float $creditoClienteMonto = 0.0;

    public float $creditoClientePropina = 0.0;

    public float $totalPropinas = 0.0;

    // Operational
    public int $totalPersonas = 0;

    public int $totalCuentas = 0;

    public float $ticketPromedio = 0.0;

    // By turno
    public float $turno1Ventas = 0.0;

    public int $turno1Personas = 0;

    public float $turno1Ticket = 0.0;

    public float $turno2Ventas = 0.0;

    public int $turno2Personas = 0;

    public float $turno2Ticket = 0.0;

    public function mount(): void
    {
        $this->period_key ??= now()->format('Y-m');
    }

    public function updatedPeriodKey(): void {}

    public function clearFilters(): void
    {
        $this->business_unit = null;
        $this->period_key = now()->format('Y-m');
    }

    private function periodRange(): array
    {
        $pk = $this->period_key;

        if (! $pk || ! preg_match('/^\d{4}-\d{2}$/', $pk)) {
            $pk = now()->format('Y-m');
            $this->period_key = $pk;
        }

        $start = \Carbon\Carbon::createFromFormat('Y-m', $pk)->startOfMonth()->toDateString();
        $end = \Carbon\Carbon::createFromFormat('Y-m', $pk)->endOfMonth()->toDateString();

        return [$start, $end];
    }

    private function salesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        [$from, $to] = $this->periodRange();

        $q = DailySale::query()
            ->where('status', 'completed')
            ->whereBetween('operation_date', [$from, $to]);

        if ($this->business_unit) {
            $q->where('business_unit', $this->business_unit);
        }

        return $q;
    }

    private function expensesQuery(): \Illuminate\Database\Eloquent\Builder
    {
        [$from, $to] = $this->periodRange();

        $q = Supply::query()
            ->join('categories', 'supplies.category_id', '=', 'categories.id')
            ->whereBetween('supplies.payment_date', [$from, $to])
            ->where('supplies.status', '!=', 'cancelado');

        if ($this->business_unit) {
            $q->where('categories.business_unit', $this->business_unit);
        }

        return $q;
    }

    protected function buildChartData(): array
    {
        $salesBase = $this->salesQuery();

        $salesTotals = (clone $salesBase)
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

        $this->totalSales = (float) ($salesTotals->total_sales ?? 0);
        $this->totalSubtotal = (float) ($salesTotals->total_subtotal ?? 0);
        $this->totalIva = (float) ($salesTotals->total_iva ?? 0);
        $this->totalAlimentos = (float) ($salesTotals->total_alimentos ?? 0);
        $this->totalBebidas = (float) ($salesTotals->total_bebidas ?? 0);
        $this->totalOtros = (float) ($salesTotals->total_otros ?? 0);

        // Payment methods
        $this->efectivoMonto = (float) ($salesTotals->total_efectivo_monto ?? 0);
        $this->efectivoPropina = (float) ($salesTotals->total_efectivo_propina ?? 0);
        $this->debitoMonto = (float) ($salesTotals->total_debito_monto ?? 0);
        $this->debitoPropina = (float) ($salesTotals->total_debito_propina ?? 0);
        $this->creditoMonto = (float) ($salesTotals->total_credito_monto ?? 0);
        $this->creditoPropina = (float) ($salesTotals->total_credito_propina ?? 0);
        $this->creditoClienteMonto = (float) ($salesTotals->total_credito_cliente_monto ?? 0);
        $this->creditoClientePropina = (float) ($salesTotals->total_credito_cliente_propina ?? 0);
        $this->totalPropinas = $this->efectivoPropina + $this->debitoPropina + $this->creditoPropina + $this->creditoClientePropina;

        // Operational
        $this->totalPersonas = (int) ($salesTotals->total_personas ?? 0);
        $this->totalCuentas = (int) ($salesTotals->total_cuentas ?? 0);
        $this->ticketPromedio = $this->totalPersonas > 0
            ? round($this->totalSales / $this->totalPersonas, 2)
            : 0;

        // Expenses
        $this->totalExpenses = (float) (clone $this->expensesQuery())
            ->selectRaw('COALESCE(SUM(ABS(supplies.amount)), 0) as total')
            ->value('total');

        $this->profit = $this->totalSales - $this->totalExpenses;

        // By turno
        $turnoData = (clone $salesBase)
            ->select('turno', DB::raw('
                COALESCE(SUM(total), 0) as total_ventas,
                COALESCE(SUM(numero_personas), 0) as total_personas
            '))
            ->groupBy('turno')
            ->get()
            ->keyBy('turno');

        $this->turno1Ventas = (float) ($turnoData[1]->total_ventas ?? 0);
        $this->turno1Personas = (int) ($turnoData[1]->total_personas ?? 0);
        $this->turno1Ticket = $this->turno1Personas > 0 ? round($this->turno1Ventas / $this->turno1Personas, 2) : 0;

        $this->turno2Ventas = (float) ($turnoData[2]->total_ventas ?? 0);
        $this->turno2Personas = (int) ($turnoData[2]->total_personas ?? 0);
        $this->turno2Ticket = $this->turno2Personas > 0 ? round($this->turno2Ventas / $this->turno2Personas, 2) : 0;

        // Sales by unit
        $byUnit = (clone $salesBase)
            ->select('business_unit', DB::raw('COALESCE(SUM(total), 0) as total_amount'))
            ->groupBy('business_unit')
            ->orderBy('business_unit')
            ->get();

        $labelsUnits = $byUnit->pluck('business_unit')->values()->toArray();
        $dataUnits = $byUnit->pluck('total_amount')->map(fn ($v) => (float) $v)->values()->toArray();

        // Payment method chart data
        $paymentLabels = ['Efectivo', 'T. Debito', 'T. Credito', 'Credito'];
        $paymentData = [
            $this->efectivoMonto,
            $this->debitoMonto,
            $this->creditoMonto,
            $this->creditoClienteMonto,
        ];

        // Expense groups for Estado de Resultados table
        [$from, $to] = $this->periodRange();
        $expenseGroups = $this->buildExpenseGroups($from, $to);

        return [
            'labelsUnits' => $labelsUnits,
            'dataUnits' => $dataUnits,
            'paymentLabels' => $paymentLabels,
            'paymentData' => $paymentData,
            'expenseGroups' => $expenseGroups,
        ];
    }

    private function buildExpenseGroups(string $from, string $to): array
    {
        // Single query: join supplies → categories → expense_types
        $rows = Supply::query()
            ->join('categories', 'supplies.category_id', '=', 'categories.id')
            ->join('expense_types', 'categories.expense_type_id', '=', 'expense_types.id')
            ->where('expense_types.is_active', true)
            ->whereBetween('supplies.payment_date', [$from, $to])
            ->where('supplies.status', '!=', 'cancelado')
            ->when($this->business_unit, fn ($q) => $q->where('categories.business_unit', $this->business_unit))
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
                'percent' => $this->totalSales > 0 ? round(((float) $row->total_amount / $this->totalSales) * 100, 2) : 0,
            ])->toArray();

            $groups[] = [
                'expense_type' => $typeName,
                'categories' => $categories,
                'total' => (float) $groupTotal,
                'percent' => $this->totalSales > 0 ? round(($groupTotal / $this->totalSales) * 100, 2) : 0,
            ];
        }

        return $groups;
    }

    public function render()
    {
        $chartData = $this->buildChartData();

        $this->dispatch('chart-data-updated', data: $chartData);

        return view('livewire.sales-dashboard', $chartData);
    }
}
