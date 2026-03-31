<?php

namespace App\Livewire;

use App\Application\DailySales\DailySalesQuery;
use App\Domain\BusinessUnit;
use App\Livewire\Concerns\HasSearchFilter;
use App\Livewire\Forms\DailySaleUploadForm;
use App\Livewire\Forms\ReconciliationForm;
use App\Models\DailySale;
use App\Services\DailySaleExtractionService;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class DailySalesController extends Component
{
    use HasSearchFilter, WithFileUploads, WithPagination;

    #[Url]
    public string $search = '';

    public string $business_unit = '';

    #[Url]
    public string $period_key = '';

    // Upload modal
    public bool $open = false;

    public DailySaleUploadForm $form;

    // Detail modal
    public bool $showDetailModal = false;

    public ?DailySale $detailSale = null;

    // Delete
    public ?int $deleteId = null;

    // Reconciliation modal
    public bool $showReconciliationModal = false;

    public ?DailySale $reconciliationSale = null;

    public ReconciliationForm $reconciliationForm;

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

    public function create(): void
    {
        $this->form->reset();
        $this->form->business_unit = BusinessUnit::Jade->value;
        $this->form->turno = 1;
        $this->resetValidation();
        $this->open = true;
    }

    /**
     * Open upload modal pre-filled for retry.
     */
    public function retry(int $id): void
    {
        $sale = DailySale::findOrFail($id);

        if (! $sale->isFailed()) {
            return;
        }

        $this->form->fillForRetry($sale);
        $this->resetValidation();
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
    }

    public function uploadPdf(): void
    {
        $this->form->validate();

        $existing = DailySale::where('business_unit', $this->form->business_unit)
            ->whereDate('operation_date', $this->form->operation_date)
            ->where('turno', $this->form->turno)
            ->first();

        if ($existing) {
            if ($existing->isCompleted()) {
                $this->addError('form.file', 'Ya existe un registro completado para esta unidad, fecha y turno.');

                return;
            }

            if ($existing->isProcessing()) {
                $this->addError('form.file', 'Ya hay un archivo en proceso para esta unidad, fecha y turno.');

                return;
            }

            $existing->delete();
        }

        try {
            $dailySale = DailySale::create([
                'business_unit' => $this->form->business_unit,
                'operation_date' => $this->form->operation_date,
                'turno' => $this->form->turno,
                'status' => 'processing',
                'user_id' => auth()->id(),
            ]);

            app(DailySaleExtractionService::class)->process($dailySale, $this->form->file);

            $this->dispatch('notify', message: 'Archivo enviado a procesar.', type: 'success');
            $this->closeModal();
        } catch (\Throwable $e) {
            $this->addError('form.file', 'Error al enviar el archivo: '.$e->getMessage());
        }
    }

    public function showDetail(int $id): void
    {
        $this->detailSale = DailySale::with('user')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailSale = null;
    }

    public function openReconciliation(int $id): void
    {
        $this->reconciliationSale = DailySale::findOrFail($id);
        $this->reconciliationForm->fillFromSale($this->reconciliationSale);
        $this->showReconciliationModal = true;
    }

    public function closeReconciliation(): void
    {
        $this->showReconciliationModal = false;
        $this->reconciliationSale = null;
        $this->reconciliationForm->reset();
    }

    public function saveReconciliation(): void
    {
        if (! $this->reconciliationSale) {
            return;
        }

        $this->reconciliationForm->validate();

        $status = $this->reconciliationForm->resolveStatus($this->reconciliationSale);

        $this->reconciliationSale->update([
            'reconciliation_status' => $status,
            'reconciliation_data' => $this->reconciliationForm->resolvedCorte(),
            'reconciliation_notes' => $this->reconciliationForm->reconciliationNotes ?: null,
            'reconciled_at' => now(),
            'reconciled_by' => auth()->id(),
        ]);

        $this->dispatch('notify',
            message: $status === 'reconciled' ? 'Cuadre completado correctamente.' : 'Cuadre guardado con diferencias.',
            type: $status === 'reconciled' ? 'success' : 'warning'
        );

        $this->closeReconciliation();
    }

    public function deleteConfirmation(int $id): void
    {
        $this->dispatch('showConfirmationModal', userId: $id)->to(ConfirmModal::class);
    }

    #[On('deleteConfirmed')]
    public function destroy(int $id): void
    {
        $sale = DailySale::findOrFail($id);

        if ($sale->isCompleted()) {
            $this->dispatch('notify', message: 'No se puede eliminar un registro completado.', type: 'error');

            return;
        }

        $sale->delete();
        $this->dispatch('notify', message: 'Registro eliminado correctamente.', type: 'success');
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->business_unit = '';
        $this->period_key = now()->format('Y-m');
        $this->resetPage();
    }

    public function render(DailySalesQuery $dailySalesQuery)
    {
        [$query, $from, $to, $this->period_key] = $dailySalesQuery->base([
            'search' => $this->search,
            'business_unit' => $this->business_unit ?: null,
            'period_key' => $this->period_key,
        ]);

        $sales = (clone $query)->with('user')->orderByDesc('operation_date')->orderBy('turno')->paginate(15);
        $totals = $dailySalesQuery->totals($query);

        return view('livewire.daily-sales-controller', [
            'sales' => $sales,
            'totals' => $totals,
            'from_date' => $from,
            'to_date' => $to,
        ]);
    }
}
