<?php

namespace App\Livewire;

use App\Models\ExpenseType;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule as VRule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseTypeController extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public bool $open = false;

    public ?int $expenseTypeId = null;

    public string $expense_type_name = '';
    public bool $is_active = true;
    public ?int $deleteId = null;

    public function rules(): array
    {
        return [
            'expense_type_name' => [
                'required', 'string', 'max:150',
                VRule::unique('expense_types', 'expense_type_name')->ignore($this->expenseTypeId),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->open = true;
    }

    public function edit(int $id): void
    {
        $category = ExpenseType::findOrFail($id);

        $this->expenseTypeId    = $category->id;
        $this->expense_type_name  = $category->expense_type_name;
        $this->is_active     = $category->is_active ? 1 : 0;

        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
    }

    // Guardar (create / update)
    public function save(): void
    {
        $data = $this->validate();

        if ($this->expenseTypeId) {
            ExpenseType::whereKey($this->expenseTypeId)->update($data);
        } else {
            ExpenseType::create($data);
        }

        $this->dispatch('notify', message: 'Categoría guardada correctamente.',type: 'success');

        $this->closeModal();
        $this->resetForm();
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

    protected function resetForm(): void
    {
        $this->expenseTypeId = null;
        $this->expense_type_name = '';
        $this->is_active = true;
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
