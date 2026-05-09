<?php

namespace App\Livewire\Expenses;

use App\Application\PurchaseOrders\PurchaseOrdersQuery;
use App\Livewire\ConfirmModal;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderGenerator;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrdersController extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $business_unit = '';

    #[Url]
    public string $status = 'closed';

    #[Url]
    public string $date_from = '';

    #[Url]
    public string $date_to = '';

    // Modal de detalle
    public bool $showDetailModal = false;

    public ?PurchaseOrder $detailOc = null;

    // Para confirmar anulación
    public ?int $cancelId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBusinessUnit(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function showDetail(int $id): void
    {
        $this->detailOc = PurchaseOrder::with([
            'supplies.category.expenseType',
            'supplies.requester',
            'supplies.approver',
            'creator',
        ])->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailOc = null;
    }

    public function cancelConfirmation(int $id): void
    {
        $this->dispatch('showConfirmationModal', userId: $id)->to(ConfirmModal::class);
    }

    #[On('deleteConfirmed')]
    public function cancel(int $id, PurchaseOrderGenerator $generator): void
    {
        $oc = PurchaseOrder::findOrFail($id);

        if ($oc->isCancelled()) {
            $this->dispatch('notify', message: 'La OC ya está anulada.', type: 'warning');

            return;
        }

        $generator->cancel($oc);

        $this->dispatch('notify', message: "OC {$oc->oc_number} anulada. Las compras quedaron liberadas.", type: 'success');

        if ($this->detailOc?->id === $id) {
            $this->closeDetail();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->business_unit = '';
        $this->status = 'closed';
        $this->date_from = '';
        $this->date_to = '';

        $this->resetPage();
    }

    public function render(PurchaseOrdersQuery $query)
    {
        $filters = [
            'search' => $this->search,
            'business_unit' => $this->business_unit,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];

        return view('livewire.expenses.purchase-orders-controller', [
            'orders' => $query->base($filters)->paginate(10),
            'totalAmount' => $query->totalAmount($filters),
            'totalClosed' => $query->totalClosed($filters),
            'totalCancelled' => $query->totalCancelled($filters),
            'totalsByUnit' => $query->totalsByUnit($filters),
        ]);
    }
}
