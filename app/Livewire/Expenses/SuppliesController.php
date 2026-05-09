<?php

namespace App\Livewire\Expenses;

use App\Application\Helpers\PeriodRange;
use App\Application\Supplies\SuppliesQuery;
use App\Livewire\Concerns\HasModalCrud;
use App\Livewire\Concerns\HasSearchFilter;
use App\Livewire\Expenses\Forms\SupplyForm;
use App\Models\Category;
use App\Models\Supply;
use App\Services\PurchaseOrderGenerator;
use App\Services\Reports\ExpensesReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

    // Modal: generar OC del día
    public bool $showGenerateOcModal = false;

    public ?string $ocPreviewDate = null;

    public int $ocPreviewCount = 0;

    public float $ocPreviewTotal = 0.0;

    // Filtros principales
    #[Url]
    public string $business_unit = '';

    #[Url]
    public string $period_key = '';         // YYYY-MM

    #[Url]
    public string $expense_type_id = '';

    #[Url]
    public string $category_id = '';

    public $categorySearch = '';

    public $categoryResults = [];

    public function mount(): void
    {
        $this->period_key = now()->format('Y-m');
    }

    public function updatedBusinessUnit(): void
    {
        // Si cambia la unidad, los filtros dependientes se invalidan.
        $this->expense_type_id = '';
        $this->category_id = '';
        $this->resetPage();
    }

    public function updatedPeriodKey(): void
    {
        $this->resetPage();
    }

    public function updatedExpenseTypeId(): void
    {
        // Cambiar el tipo limpia la categoría seleccionada (cascada).
        $this->category_id = '';
        $this->resetPage();
    }

    public function updatedCategoryId(): void
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
            'expense_type_id' => $this->expense_type_id,
            'category_id' => $this->category_id,
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
            'expense_type_id' => $this->expense_type_id,
            'category_id' => $this->category_id,
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

    private function resetCategorySearch(): void
    {
        $this->categorySearch = '';
        $this->categoryResults = [];
    }

    public function create(): void
    {
        $this->form->reset();
        $this->resetCategorySearch();
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetCategorySearch();
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $supply = Supply::with(['purchaseOrder', 'category.expenseType'])->findOrFail($id);

        if ($supply->isLocked()) {
            $this->dispatch('notify', message: 'Esta compra está asociada a una OC cerrada y no puede editarse. Anulá la OC para liberarla.', type: 'warning');

            return;
        }

        $this->resetCategorySearch();
        $this->form->fillFromModel($supply);
        $this->categorySearch = $this->buildCategoryLabel($supply->category);
        $this->open = true;
    }

    public function updatedFormBusinessUnit(): void
    {
        // Cambiar la unidad invalida la categoría seleccionada (no tiene sentido cross-unidad).
        $this->form->category_id = '';
        $this->resetCategorySearch();
    }

    private function buildCategoryLabel(?Category $cat): string
    {
        if (! $cat) {
            return '';
        }

        $label = "[{$cat->business_unit}] ".($cat->expenseType?->expense_type_name ?? '—')." — {$cat->expense_name}";

        if ($cat->provider_name) {
            $label .= " · {$cat->provider_name}";
        }

        return $label;
    }

    // Guardar (create / update)
    public function save(): void
    {
        $validated = $this->form->validate();

        // Validación cruzada: la categoría debe pertenecer a la unidad seleccionada.
        $cat = Category::find($validated['category_id']);
        if ($cat && $cat->business_unit !== $validated['business_unit']) {
            $this->form->addError('category_id', 'La categoría seleccionada no pertenece a la unidad de negocio elegida.');

            return;
        }

        // business_unit es sólo del form (filtra el dropdown de categoría); no se persiste en supplies.
        unset($validated['business_unit']);

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
        $this->resetCategorySearch();
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
        $supply = Supply::with('purchaseOrder')->findOrFail($id);

        if ($supply->isLocked()) {
            $this->dispatch('notify', message: 'Esta compra pertenece a una OC cerrada y no puede eliminarse.', type: 'warning');

            return;
        }

        if ($supply->receipt_path) {
            Storage::disk('public')->delete($supply->receipt_path);
        }

        $supply->delete();

        $this->dispatch('notify', message: 'El registro se eliminó con éxito.', type: 'success');
    }

    /**
     * Abre el modal de preview con la fecha de hoy por defecto.
     * El usuario puede cambiar la fecha dentro del modal para generar OCs de días pasados.
     */
    public function previewGeneratePurchaseOrder(): void
    {
        if (! $this->business_unit) {
            $this->dispatch('notify', message: 'Seleccioná una unidad de negocio antes de generar la OC.', type: 'warning');

            return;
        }

        $this->ocPreviewDate = Carbon::today()->toDateString();
        $this->refreshOcPreview();
        $this->showGenerateOcModal = true;
    }

    public function updatedOcPreviewDate(): void
    {
        $this->refreshOcPreview();
    }

    private function refreshOcPreview(): void
    {
        if (! $this->business_unit || ! $this->ocPreviewDate) {
            $this->ocPreviewCount = 0;
            $this->ocPreviewTotal = 0.0;

            return;
        }

        $eligible = app(PurchaseOrderGenerator::class)
            ->eligibleSupplies($this->business_unit, Carbon::parse($this->ocPreviewDate));

        $this->ocPreviewCount = $eligible->count();
        $this->ocPreviewTotal = (float) $eligible->sum('amount');
    }

    public function confirmGeneratePurchaseOrder(PurchaseOrderGenerator $generator): void
    {
        if (! $this->business_unit || ! $this->ocPreviewDate) {
            $this->showGenerateOcModal = false;

            return;
        }

        try {
            $oc = $generator->generate(
                $this->business_unit,
                Carbon::parse($this->ocPreviewDate),
                (int) Auth::id(),
            );

            $this->dispatch('notify', message: "OC {$oc->oc_number} generada con éxito.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'warning');
        }

        $this->showGenerateOcModal = false;
        $this->ocPreviewDate = null;
        $this->ocPreviewCount = 0;
        $this->ocPreviewTotal = 0.0;
    }

    public function closeGenerateOcModal(): void
    {
        $this->showGenerateOcModal = false;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->business_unit = '';
        $this->expense_type_id = '';
        $this->category_id = '';
        $this->period_key = now()->format('Y-m');

        $this->resetPage();
    }

    public function updatedCategorySearch(SuppliesQuery $query): void
    {
        if (! $this->form->business_unit) {
            $this->categoryResults = [];

            return;
        }

        if (strlen($this->categorySearch) < 2) {
            $this->categoryResults = [];

            return;
        }

        $this->categoryResults = $query->searchCategories($this->categorySearch, $this->form->business_unit)
            ->map(fn ($c) => [
                'id' => $c->id,
                'business_unit' => $c->business_unit,
                'expense_type_name' => $c->expenseType?->expense_type_name ?? '—',
                'expense_name' => $c->expense_name,
                'provider_name' => $c->provider_name,
            ])
            ->toArray();
    }

    public function selectCategory(int $id): void
    {
        $cat = collect($this->categoryResults)->firstWhere('id', $id);

        if (! $cat) {
            return;
        }

        $this->form->category_id = (string) $id;

        $this->categorySearch = "[{$cat['business_unit']}] "
            .$cat['expense_type_name']
            ." — {$cat['expense_name']}"
            .($cat['provider_name'] ? " · {$cat['provider_name']}" : '');

        $this->categoryResults = [];
    }

    public function render(SuppliesQuery $query)
    {
        [$from, $to, $this->period_key] = PeriodRange::fromKey($this->period_key);

        // Filtros base que TODOS los dropdowns y la tabla respetan.
        $baseFilters = [
            'search' => $this->search,
            'business_unit' => $this->business_unit,
            'date_from' => $from,
            'date_to' => $to,
        ];

        // Filtros completos para la tabla y las cards (incluyen tipo y categoría).
        $tableFilters = $baseFilters + [
            'expense_type_id' => $this->expense_type_id,
            'category_id' => $this->category_id,
        ];

        // El dropdown de tipo NO se filtra por sí mismo ni por la categoría seleccionada.
        $expenseTypes = $query->expenseTypesForFilter(
            $baseFilters,
            $this->expense_type_id ?: null,
        );

        // El dropdown de categoría sí respeta el tipo seleccionado (cascada), pero no a sí mismo.
        $categories = $query->categoriesForFilter(
            $baseFilters + ['expense_type_id' => $this->expense_type_id],
            $this->category_id ?: null,
        );

        $supplies = $query->base($tableFilters)->paginate(10);

        return view('livewire.expenses.supplies-controller', [
            'supplies' => $supplies,
            'totalGeneral' => $query->totalGeneral($tableFilters),
            'totalsByType' => $query->totalsByExpenseType($tableFilters),
            'cancelledTotal' => $query->cancelledTotal($tableFilters),
            'cancelledCount' => $query->cancelledCount($tableFilters),
            'expenseTypes' => $expenseTypes,
            'categories' => $categories,
            'from_date' => $from,
            'to_date' => $to,
            'periodKey' => $this->period_key,
            'businessUnit' => $this->business_unit,
        ]);
    }
}
