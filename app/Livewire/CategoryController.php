<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\ExpenseType;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule as VRule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryController extends Component
{
    use WithPagination;
    public const BUSINESS_UNITS = ['Jade', 'Fuego Ambar', 'KIN'];

    #[Url]
    public string $search = '';

    public bool $open = false;

    public ?int $categoryId = null;

    public string $business_unit = '';
    public string $expense_name = '';
    public ?int $expense_type_id = null;
    public string $provider_name = '';
    public bool $is_active = true;

    public function rules(): array
    {
        return [
            'business_unit'   => ['required', 'string', 'max:150', VRule::in(self::BUSINESS_UNITS)],
            'expense_name'    => ['required', 'string', 'max:150'],
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'provider_name'   => ['required', 'string', 'max:150'],
            'is_active'       => ['required', 'boolean'],
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
        $category = Category::findOrFail($id);

        $this->categoryId      = $category->id;
        $this->business_unit   = $category->business_unit;
        $this->expense_type_id = $category->expense_type_id;
        $this->expense_name    = $category->expense_name;
        $this->provider_name   = $category->provider_name;
        $this->is_active       = (bool) $category->is_active;

        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->categoryId) {
            Category::whereKey($this->categoryId)->update($data);
        } else {
            Category::create($data);
        }

         $this->dispatch('notify', message: 'Categoría guardada correctamente.',type: 'success');

        $this->closeModal();
        $this->resetForm();
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

    protected function resetForm(): void
    {
        $this->categoryId      = null;
        $this->business_unit   = '';
        $this->expense_name    = '';
        $this->expense_type_id = null;
        $this->provider_name   = '';
        $this->is_active       = true;
    }

    public function render()
    {
        $categories = Category::query()
            ->with('expenseType')
            ->when(trim($this->search) !== '', function ($q) {
                $s = trim($this->search);
                $q->where(fn ($qq) =>
                $qq->where('business_unit', 'like', "%{$s}%")
                    ->orWhere('expense_name', 'like', "%{$s}%")
                    ->orWhere('provider_name', 'like', "%{$s}%")
                );
            })
            ->orderBy('business_unit')
            ->orderBy('expense_name')
            ->paginate(10);

        $expenseType = ExpenseType::orderBy('expense_type_name')->get(['id', 'expense_type_name']);

        return view('livewire.category-controller', compact('categories', 'expenseType'));
    }
}
