<?php

namespace App\Livewire;

use App\Application\Supplies\SuppliesQuery;
use App\Models\IncomePeriod;
use App\Models\Supply;
use App\Services\Reports\ExpensesReportService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SuppliesController extends Component
{
    use WithPagination;

    // Buscador sincronizado con la URL
    #[Url]
    public string $search = '';

    // (Ya no se usan para filtrar la tabla, pero los dejo por compatibilidad si tu UI los usa)
    #[Rule('nullable|date')]
    public ?string $from_date = null;

    #[Rule('nullable|date')]
    public ?string $to_date = null;

    // Control del modal create/edit
    public bool $open = false;

    // Modal de detalle
    public bool $showDetailModal = false;
    public ?Supply $detailSupply = null;

    // Id del registro actual (para editar)
    public ?int $supplyId = null;

    // Campos del formulario
    #[Rule('required|exists:categories,id')]
    public $category_id = '';

    #[Rule('required|numeric|min:0')]
    public $amount = '';

    #[Rule('boolean')]
    public bool $is_adjustment = false;

    #[Rule('nullable|in:efectivo,transferencia,tarjeta_credito,tarjeta_debito,cheque,otro')]
    public ?string $payment_type = null;

    #[Rule('nullable|date')]
    public ?string $payment_date = null;

    #[Rule('required|in:pendiente,pagado,cancelado')]
    public ?string $status = 'pendiente';

    #[Rule('nullable|string|max:1000')]
    public ?string $notes = null;

    // Para confirmar eliminación (igual que Users)
    public ?int $deleteId = null;

    // Filtros principales
    public string $business_unit = 'Jade';  // ✅ consistente con DB/UI
    public string $period_key = '';         // YYYY-MM

    // Modal ingreso mensual
    public bool $openIncomeModal = false;

    public ?string $income_id = null;
    public ?float $income_amount = null;
    public ?string $income_notes = null;

    public ?IncomePeriod $currentIncome = null;

    public function mount(): void
    {
        $this->period_key = now()->format('Y-m');
        $this->loadIncome();
    }

    private function periodRange(): array
    {
        $pk = $this->period_key;

        if (empty($pk) || !preg_match('/^\d{4}-\d{2}$/', $pk)) {
            $pk = now()->format('Y-m');
            $this->period_key = $pk;
        }

        $start = Carbon::createFromFormat('Y-m', $pk)->startOfMonth()->toDateString();
        $end   = Carbon::createFromFormat('Y-m', $pk)->endOfMonth()->toDateString();

        return [$start, $end];
    }

    protected function loadIncome(): void
    {
        $this->currentIncome = IncomePeriod::query()
            ->where('business_unit', $this->business_unit)
            ->where('period_key', $this->period_key)
            ->first();
    }

    public function openIncome(): void
    {
        $this->loadIncome();

        if ($this->currentIncome) {
            $this->income_id = (string) $this->currentIncome->id;
            $this->income_amount = (float) $this->currentIncome->income_amount;
            $this->income_notes = $this->currentIncome->notes;
        } else {
            $this->income_id = null;
            $this->income_amount = null;
            $this->income_notes = null;
        }

        $this->openIncomeModal = true;
    }

    public function closeIncome(): void
    {
        $this->openIncomeModal = false;
        $this->resetValidation();
    }

    public function saveIncome(): void
    {
        $this->validate([
            'business_unit'  => 'required|in:Jade,Fuego Ambar,KIN',
            'period_key'     => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'income_amount'  => 'required|numeric|min:0',
            'income_notes'   => 'nullable|string|max:1000',
        ]);

        IncomePeriod::updateOrCreate(
            [
                'business_unit' => $this->business_unit,
                'period_key' => $this->period_key,
            ],
            [
                'income_amount' => $this->income_amount,
                'notes' => $this->income_notes,
                'user_id' => auth()->id(),
            ]
        );

        $this->loadIncome();
        $this->openIncomeModal = false;

        $this->dispatch('notify', message: 'Ingreso mensual guardado correctamente.', type: 'success');
    }

    public function updatedBusinessUnit(): void
    {
        $this->resetPage();
        $this->loadIncome();
    }

    public function updatedPeriodKey(): void
    {
        $this->resetPage();
        $this->loadIncome();
    }

    public function setCurrentMonth(): void
    {
        $this->period_key = now()->format('Y-m');
        $this->resetPage();
        $this->loadIncome();
    }

    /**
     * ✅ Export 1:1 con la tabla:
     * - construimos el MISMO baseQuery (SuppliesQuery)
     * - sacamos IDs exactos
     * - armamos data con buildReportDataFromSupplyIds()
     */
    public function exportExcel(SuppliesQuery $query, ExpensesReportService $service)
    {
        [$from, $to] = $this->periodRange();

        $filters = [
            'search' => $this->search,
            'business_unit' => $this->business_unit,
            'date_from' => $from,
            'date_to' => $to,
        ];

        $baseQuery = $query->base($filters);

        // IDs exactos (rápido y seguro)
        $ids = (clone $baseQuery)
            ->select('supplies.id')
            ->pluck('id')
            ->all();

        $data = $service->buildReportDataFromSupplyIds(
            supplyIds: $ids,
            businessUnit: $this->business_unit,
            fromDate: $from,
            toDate: $to,
            periodKey: $this->period_key
        );

        $name = 'reporte_gastos_'.$this->business_unit.'_'.now()->format('Y-m-d').'.xlsx';

        return $service->downloadExcel($data, $name);
    }

    public function exportPdf(SuppliesQuery $query, ExpensesReportService $service)
    {
        [$from, $to] = $this->periodRange();

        $filters = [
            'search' => $this->search,
            'business_unit' => $this->business_unit,
            'date_from' => $from,
            'date_to' => $to,
        ];

        $baseQuery = $query->base($filters);

        $ids = (clone $baseQuery)
            ->select('supplies.id')
            ->pluck('id')
            ->all();

        $data = $service->buildReportDataFromSupplyIds(
            supplyIds: $ids,
            businessUnit: $this->business_unit,
            fromDate: $from,
            toDate: $to,
            periodKey: $this->period_key
        );

        $name = 'reporte_gastos_'.$this->business_unit.'_'.now()->format('Y-m-d').'.pdf';

        return $service->downloadPdf($data, $name);
    }

    // Al cambiar buscador, volver a página 1
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function showDetail(int $id): void
    {
        $this->detailSupply = Supply::with(['category.expenseType'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailSupply = null;
    }

    // Abrir modal en modo "crear"
    public function create(): void
    {
        $this->resetForm();
        $this->open = true;
    }

    public function clearPeriodFilter(): void
    {
        $this->period_key = now()->format('Y-m');
        $this->resetPage();
        $this->loadIncome();
    }

    // Abrir modal en modo "editar"
    public function edit(int $id): void
    {
        $supply = Supply::findOrFail($id);

        $this->supplyId         = $supply->id;
        $this->category_id      = $supply->category_id;
        $this->is_adjustment    = ((float) $supply->amount) < 0;
        $this->amount           = abs((float) $supply->amount);
        $this->payment_type     = $supply->payment_type;
        $this->payment_date     = $supply->payment_date?->format('Y-m-d');
        $this->status           = $supply->status;
        $this->notes            = $supply->notes;

        $this->open = true;
    }

    // Cerrar modal
    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
    }

    // Guardar (create / update)
    public function save(): void
    {
        $validated = $this->validate();

        // payment_month automático:
        if (!empty($validated['payment_date'])) {
            $date = Carbon::parse($validated['payment_date']);
            $validated['payment_month'] = $date->format('Y-m'); // Ej: 2025-02
        } else {
            $validated['payment_month'] = null;
        }

        $amount = (float) $validated['amount'];

        // Si es ajuste, lo guardamos negativo
        if ($this->is_adjustment && $amount > 0) {
            $amount = -1 * $amount;
        }

        // Si NO es ajuste y viene negativo, lo corregimos a positivo
        if (! $this->is_adjustment && $amount < 0) {
            $amount = abs($amount);
        }

        $validated['amount'] = $amount;

        Supply::updateOrCreate(
            ['id' => $this->supplyId],
            $validated,
        );

        $this->dispatch('notify', message: 'El registro se guardó correctamente.', type: 'success');

        $this->closeModal();
        $this->resetForm();
    }

    // Preparar eliminación (igual que Users)
    public function deleteConfirmation(int $id): void
    {
        $this->dispatch('showConfirmationModal', userId: $id)->to(ConfirmModal::class);

    }

    #[On('deleteConfirmed')]
    public function destroy(int $id): void
    {
        Supply::findOrFail($id)->delete();

        $this->dispatch('notify', message: 'El registro se eliminó con éxito.', type: 'success');
    }

    // Resetear campos de formulario
    protected function resetForm(): void
    {
        $this->supplyId      = null;
        $this->category_id   = '';
        $this->amount        = '';
        $this->payment_type  = null;
        $this->payment_date  = null;
        $this->status        = 'pendiente';
        $this->notes         = null;
        $this->is_adjustment = false;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->business_unit = '';
        $this->period_key = now()->format('Y-m');

        $this->resetPage();
    }


    public function render(SuppliesQuery $query)
    {
        [$from, $to] = $this->periodRange();

        $filters = [
            'search' => $this->search,
            'business_unit' => $this->business_unit,
            'date_from' => $from,
            'date_to' => $to,
        ];

        $baseQuery = $query->base($filters);

        $supplies     = $baseQuery->paginate(10);
        $totalsByUnit = $query->totalsByUnit($filters);
        $incomePeriod = $query->incomePeriod($this->business_unit, $this->period_key);
        $categories   = $query->categories();

        return view('livewire.supplies-controller', [
            'supplies'      => $supplies,
            'categories'    => $categories,
            'totalsByUnit'  => $totalsByUnit,
            'incomePeriod'  => $incomePeriod,

            // útil para mostrar en UI
            'from_date'     => $from,
            'to_date'       => $to,
            'periodKey'     => $this->period_key,
            'businessUnit'  => $this->business_unit,
        ]);
    }
}
