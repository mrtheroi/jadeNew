<?php

namespace App\Livewire;

use App\Application\Helpers\PeriodRange;
use App\Application\Supplies\SuppliesQuery;
use App\Livewire\Concerns\HasModalCrud;
use App\Livewire\Concerns\HasSearchFilter;
use App\Livewire\Forms\SupplyForm;
use App\Models\Supply;
use App\Services\Reports\ExpensesReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SuppliesController extends Component
{
    use HasModalCrud, HasSearchFilter, WithFileUploads, WithPagination;

    // Buscador sincronizado con la URL
    #[Url]
    public string $search = '';

    public SupplyForm $form;

    // Modal de detalle
    public bool $showDetailModal = false;

    public ?Supply $detailSupply = null;

    // Modal para ver comprobante
    public bool $showReceiptModal = false;

    public ?string $receiptUrl = null;

    // Para confirmar eliminación (igual que Users)
    public ?int $deleteId = null;

    // Filtros principales
    public string $business_unit = '';

    public string $period_key = '';         // YYYY-MM

    public function mount(): void
    {
        $this->period_key = now()->format('Y-m');
    }

    public function updatedBusinessUnit(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodKey(): void
    {
        $this->resetPage();
    }

    public function setCurrentMonth(): void
    {
        $this->period_key = now()->format('Y-m');
        $this->resetPage();
    }

    /**
     * ✅ Export 1:1 con la tabla:
     * - construimos el MISMO baseQuery (SuppliesQuery)
     * - sacamos IDs exactos
     * - armamos data con buildReportDataFromSupplyIds()
     */
    public function exportExcel(SuppliesQuery $query, ExpensesReportService $service)
    {
        [$from, $to, $this->period_key] = PeriodRange::fromKey($this->period_key);

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
        [$from, $to, $this->period_key] = PeriodRange::fromKey($this->period_key);

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

    public function showReceipt(int $id): void
    {
        $supply = Supply::findOrFail($id);

        if (! $supply->receipt_path) {
            return;
        }

        $this->receiptUrl = Storage::disk('public')->url($supply->receipt_path);
        $this->showReceiptModal = true;
    }

    public function closeReceipt(): void
    {
        $this->showReceiptModal = false;
        $this->receiptUrl = null;
    }

    public function clearPeriodFilter(): void
    {
        $this->period_key = now()->format('Y-m');
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $this->form->fillFromModel(Supply::findOrFail($id));
        $this->open = true;
    }

    // Guardar (create / update)
    public function save(): void
    {
        $validated = $this->form->validate();

        // payment_month automatico
        if (! empty($validated['payment_date'])) {
            $date = Carbon::parse($validated['payment_date']);
            $validated['payment_month'] = $date->format('Y-m');
        } else {
            $validated['payment_month'] = null;
        }

        $validated['amount'] = $this->form->resolvedAmount();

        $supply = Supply::updateOrCreate(
            ['id' => $this->form->supplyId],
            $validated,
        );

        // Handle receipt image
        if ($this->form->receipt) {
            if ($supply->receipt_path) {
                Storage::disk('public')->delete($supply->receipt_path);
            }

            $path = $this->form->receipt->storeAs(
                'receipts',
                $supply->id.'_'.now()->timestamp.'.'.$this->form->receipt->getClientOriginalExtension(),
                'public'
            );

            $supply->update(['receipt_path' => $path]);
        } elseif ($this->form->removeReceipt && $supply->receipt_path) {
            Storage::disk('public')->delete($supply->receipt_path);
            $supply->update(['receipt_path' => null]);
        }

        $this->dispatch('notify', message: 'El registro se guardó correctamente.', type: 'success');

        $this->closeModal();
        $this->form->reset();
    }

    // Preparar eliminación (igual que Users)
    public function deleteConfirmation(int $id): void
    {
        $this->dispatch('showConfirmationModal', userId: $id)->to(ConfirmModal::class);

    }

    #[On('deleteConfirmed')]
    public function destroy(int $id): void
    {
        $supply = Supply::findOrFail($id);

        if ($supply->receipt_path) {
            Storage::disk('public')->delete($supply->receipt_path);
        }

        $supply->delete();

        $this->dispatch('notify', message: 'El registro se eliminó con éxito.', type: 'success');
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
        [$from, $to, $this->period_key] = PeriodRange::fromKey($this->period_key);

        $filters = [
            'search' => $this->search,
            'business_unit' => $this->business_unit,
            'date_from' => $from,
            'date_to' => $to,
        ];

        $baseQuery = $query->base($filters);

        $supplies = $baseQuery->paginate(10);
        $totalsByUnit = $query->totalsByUnit($filters);
        $categories = $query->categories();

        return view('livewire.supplies-controller', [
            'supplies' => $supplies,
            'categories' => $categories,
            'totalsByUnit' => $totalsByUnit,
            'from_date' => $from,
            'to_date' => $to,
            'periodKey' => $this->period_key,
            'businessUnit' => $this->business_unit,
        ]);
    }
}
