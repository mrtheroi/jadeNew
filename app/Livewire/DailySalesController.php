<?php

namespace App\Livewire;

use App\Jobs\ProcessLlamaExtractionJob;
use App\Models\DailySale;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class DailySalesController extends Component
{
    use WithFileUploads, WithPagination;

    #[Url]
    public string $search = '';

    public string $filterBusinessUnit = '';

    #[Url]
    public string $period_key = '';

    // Upload modal
    public bool $open = false;

    #[Rule('required|in:Jade,Fuego Ambar,KIN')]
    public string $business_unit = 'Jade';

    #[Rule('required|date')]
    public ?string $operation_date = null;

    #[Rule('required|in:1,2')]
    public int $turno = 1;

    #[Rule('required|file|mimes:pdf|max:10240')]
    public $file;

    // Detail modal
    public bool $showDetailModal = false;

    public ?DailySale $detailSale = null;

    // Delete
    public ?int $deleteId = null;

    public function mount(): void
    {
        $this->period_key = now()->format('Y-m');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBusinessUnit(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodKey(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['file', 'operation_date', 'turno']);
        $this->business_unit = 'Jade';
        $this->turno = 1;
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

        $this->business_unit = $sale->business_unit;
        $this->operation_date = $sale->operation_date->format('Y-m-d');
        $this->turno = $sale->turno;
        $this->reset(['file']);
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
        $this->validate();

        // Check if completed record already exists for this combo
        $existing = DailySale::where('business_unit', $this->business_unit)
            ->where('operation_date', $this->operation_date)
            ->where('turno', $this->turno)
            ->first();

        if ($existing && $existing->isCompleted()) {
            $this->addError('file', 'Ya existe un registro completado para esta unidad, fecha y turno.');

            return;
        }

        // If failed record exists, delete it for retry
        if ($existing && $existing->isFailed()) {
            $existing->delete();
        }

        // If processing record exists, don't allow another upload
        if ($existing && $existing->isProcessing()) {
            $this->addError('file', 'Ya hay un archivo en proceso para esta unidad, fecha y turno.');

            return;
        }

        try {
            // Store file temporarily
            $tempPath = $this->file->store('tmp-extractions');
            $fullPath = storage_path('app/private/'.$tempPath);
            $fileName = $this->file->getClientOriginalName();

            // Create DailySale with processing status
            $dailySale = DailySale::create([
                'business_unit' => $this->business_unit,
                'operation_date' => $this->operation_date,
                'turno' => $this->turno,
                'status' => 'processing',
                'user_id' => auth()->id(),
            ]);

            // Dispatch job
            ProcessLlamaExtractionJob::dispatch($dailySale, $fullPath, $fileName);

            $this->dispatch('notify', message: 'Archivo enviado a procesar.', type: 'success');
            $this->closeModal();
        } catch (\Throwable $e) {
            $this->addError('file', 'Error al enviar el archivo: '.$e->getMessage());
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
        $this->filterBusinessUnit = '';
        $this->period_key = now()->format('Y-m');
        $this->resetPage();
    }

    private function periodRange(): array
    {
        $pk = $this->period_key;

        if (empty($pk) || ! preg_match('/^\d{4}-\d{2}$/', $pk)) {
            $pk = now()->format('Y-m');
            $this->period_key = $pk;
        }

        $start = \Carbon\Carbon::createFromFormat('Y-m', $pk)->startOfMonth()->toDateString();
        $end = \Carbon\Carbon::createFromFormat('Y-m', $pk)->endOfMonth()->toDateString();

        return [$start, $end];
    }

    public function render()
    {
        [$from, $to] = $this->periodRange();

        $query = DailySale::query()
            ->whereBetween('operation_date', [$from, $to]);

        if ($this->filterBusinessUnit) {
            $query->where('business_unit', $this->filterBusinessUnit);
        }

        if ($this->search) {
            $query->where('business_unit', 'like', "%{$this->search}%");
        }

        $sales = $query->with('user')->orderByDesc('operation_date')->orderBy('turno')->paginate(15);

        $totalsQuery = (clone $query)->where('status', 'completed');

        $totals = $totalsQuery->selectRaw('
            SUM(alimentos) as total_alimentos,
            SUM(bebidas) as total_bebidas,
            SUM(otros) as total_otros,
            SUM(subtotal) as total_subtotal,
            SUM(iva) as total_iva,
            SUM(total) as total_total
        ')->first();

        return view('livewire.daily-sales-controller', [
            'sales' => $sales,
            'totals' => $totals,
            'from_date' => $from,
            'to_date' => $to,
        ]);
    }
}
