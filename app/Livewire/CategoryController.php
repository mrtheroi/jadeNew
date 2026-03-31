<?php

namespace App\Livewire;

use App\Domain\BusinessUnit;
use App\Livewire\Concerns\HasModalCrud;
use App\Livewire\Concerns\HasSearchFilter;
use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use App\Models\ExpenseType;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryController extends Component
{
    use HasModalCrud, HasSearchFilter, WithPagination;

    public CategoryForm $form;

    #[Url]
    public string $search = '';

    public function edit(int $id): void
    {
        $this->form->fillFromModel(Category::findOrFail($id));
        $this->open = true;
    }

    public function save(): void
    {
        $data = $this->form->validate();

        if ($this->form->categoryId) {
            Category::whereKey($this->form->categoryId)->update($data);
        } else {
            Category::create($data);
        }

        $this->dispatch('notify', message: 'Categoría guardada correctamente.', type: 'success');

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
        Category::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'La categoría se eliminó con éxito.', type: 'success');
    }

    public function render()
    {
        $categories = Category::query()
            ->with('expenseType')
            ->when(trim($this->search) !== '', function ($q) {
                $s = trim($this->search);
                $q->where(fn ($qq) => $qq->where('business_unit', 'like', "%{$s}%")
                    ->orWhere('expense_name', 'like', "%{$s}%")
                    ->orWhere('provider_name', 'like', "%{$s}%")
                );
            })
            ->orderBy('business_unit')
            ->orderBy('expense_name')
            ->paginate(10);

        $expenseType = ExpenseType::orderBy('expense_type_name')->get(['id', 'expense_type_name']);

        $businessUnits = BusinessUnit::cases();

        return view('livewire.category-controller', compact('categories', 'expenseType', 'businessUnits'));
    }
}
