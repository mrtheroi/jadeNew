<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasModalCrud;
use App\Livewire\Concerns\HasSearchFilter;
use App\Livewire\Forms\ExpenseTypeForm;
use App\Models\ExpenseType;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseTypeController extends Component
{
    use HasModalCrud, HasSearchFilter, WithPagination;

    #[Url]
    public string $search = '';

    public ExpenseTypeForm $form;

    public function edit(int $id): void
    {
        $this->form->fillFromModel(ExpenseType::findOrFail($id));
        $this->open = true;
    }

    // Guardar (create / update)
    public function save(): void
    {
        $data = $this->form->validate();

        if ($this->form->expenseTypeId) {
            ExpenseType::whereKey($this->form->expenseTypeId)->update($data);
        } else {
            ExpenseType::create($data);
        }

        $this->dispatch('notify', message: 'Tipo de gasto guardado correctamente.', type: 'success');

        $this->closeModal();
        $this->form->reset();
    }

    // Preparar eliminación
    public function deleteConfirmation($id): void
    {
        $this->dispatch('showConfirmationModal', userId: $id)->to(ConfirmModal::class);

    }

    #[On('deleteConfirmed')]
    public function destroy(int $id): void
    {
        ExpenseType::findOrFail($id)->delete();

        $this->dispatch('notify', message: 'Tipo de gasto eliminado con éxito.', type: 'success');
    }

    public function render()
    {
        $expenseTypes = ExpenseType::query()
            ->when(trim($this->search) !== '', function ($q) {
                $s = trim($this->search);
                $q->where('expense_type_name', 'like', "%{$s}%");
            })
            ->orderBy('expense_type_name')
            ->paginate(10);

        return view('livewire.expense-type', [
            'expenseTypes' => $expenseTypes,
        ]);
    }
}
