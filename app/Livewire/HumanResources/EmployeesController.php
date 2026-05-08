<?php

namespace App\Livewire\HumanResources;

use App\Application\HumanResources\Employees\EmployeesQuery;
use App\Livewire\Concerns\HasModalCrud;
use App\Livewire\Concerns\HasSearchFilter;
use App\Livewire\ConfirmModal;
use App\Livewire\HumanResources\Forms\EmployeeForm;
use App\Models\Employee;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeesController extends Component
{
    use HasModalCrud, HasSearchFilter, WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $business_unit = '';

    #[Url]
    public string $status = 'active';

    public EmployeeForm $form;

    // Modal de detalle
    public bool $showDetailModal = false;

    public ?Employee $detailEmployee = null;

    public ?int $deleteId = null;

    public function updatedBusinessUnit(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function showDetail(int $id): void
    {
        $this->detailEmployee = Employee::findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailEmployee = null;
    }

    public function edit(int $id): void
    {
        $this->form->fillFromModel(Employee::findOrFail($id));
        $this->open = true;
    }

    public function save(): void
    {
        $validated = $this->form->validate();

        // Si quedó como activo, limpiamos terminated_at por consistencia.
        if ($this->form->is_active) {
            $validated['terminated_at'] = null;
        }

        Employee::updateOrCreate(
            ['id' => $this->form->selected_id],
            $validated,
        );

        $this->dispatch('notify', message: 'El empleado se guardó correctamente.', type: 'success');

        $this->closeModal();
        $this->form->reset();
    }

    public function deleteConfirmation(int $id): void
    {
        $this->dispatch('showConfirmationModal', userId: $id)->to(ConfirmModal::class);
    }

    #[On('deleteConfirmed')]
    public function destroy(int $id): void
    {
        Employee::findOrFail($id)->delete();

        $this->dispatch('notify', message: 'El empleado se eliminó con éxito.', type: 'success');
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->business_unit = '';
        $this->status = 'active';

        $this->resetPage();
    }

    public function render(EmployeesQuery $query)
    {
        $filters = [
            'search' => $this->search,
            'business_unit' => $this->business_unit,
            'status' => $this->status,
        ];

        return view('livewire.human-resources.employees-controller', [
            'employees' => $query->base($filters)->paginate(10),
            'totalActive' => $query->totalActive($filters),
            'totalInactive' => $query->totalInactive($filters),
            'totalsByUnit' => $query->totalsByUnit($filters),
        ]);
    }
}
