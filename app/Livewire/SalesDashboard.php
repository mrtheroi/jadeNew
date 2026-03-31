<?php

namespace App\Livewire;

use App\Services\DashboardService;
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

    public function render(DashboardService $dashboardService)
    {
        $data = $dashboardService->getFullDashboardData($this->business_unit, $this->period_key);

        $this->period_key = $data['periodKey'];

        // Populate public properties for the view
        $this->totalSales = $data['totalSales'];
        $this->totalSubtotal = $data['totalSubtotal'];
        $this->totalIva = $data['totalIva'];
        $this->totalAlimentos = $data['totalAlimentos'];
        $this->totalBebidas = $data['totalBebidas'];
        $this->totalOtros = $data['totalOtros'];
        $this->totalExpenses = $data['totalExpenses'];
        $this->profit = $data['profit'];
        $this->efectivoMonto = $data['efectivoMonto'];
        $this->efectivoPropina = $data['efectivoPropina'];
        $this->debitoMonto = $data['debitoMonto'];
        $this->debitoPropina = $data['debitoPropina'];
        $this->creditoMonto = $data['creditoMonto'];
        $this->creditoPropina = $data['creditoPropina'];
        $this->creditoClienteMonto = $data['creditoClienteMonto'];
        $this->creditoClientePropina = $data['creditoClientePropina'];
        $this->totalPropinas = $data['totalPropinas'];
        $this->totalPersonas = $data['totalPersonas'];
        $this->totalCuentas = $data['totalCuentas'];
        $this->ticketPromedio = $data['ticketPromedio'];
        $this->turno1Ventas = $data['turno1Ventas'];
        $this->turno1Personas = $data['turno1Personas'];
        $this->turno1Ticket = $data['turno1Ticket'];
        $this->turno2Ventas = $data['turno2Ventas'];
        $this->turno2Personas = $data['turno2Personas'];
        $this->turno2Ticket = $data['turno2Ticket'];

        $chartData = [
            'labelsUnits' => $data['labelsUnits'],
            'dataUnits' => $data['dataUnits'],
            'paymentLabels' => $data['paymentLabels'],
            'paymentData' => $data['paymentData'],
            'expenseGroups' => $data['expenseGroups'],
        ];

        $this->dispatch('chart-data-updated', data: $chartData);

        return view('livewire.sales-dashboard', $chartData);
    }
}
