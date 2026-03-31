<?php

namespace App\Livewire\Forms;

use App\Domain\BusinessUnit;
use App\Models\Category;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Rule;
use Livewire\Form;

class CategoryForm extends Form
{
    public ?int $categoryId = null;

    #[Rule(['required', new Enum(BusinessUnit::class)])]
    public string $business_unit = '';

    #[Rule('required|string|max:150')]
    public string $expense_name = '';

    #[Rule('required|exists:expense_types,id')]
    public ?int $expense_type_id = null;

    #[Rule('required|string|max:150')]
    public string $provider_name = '';

    #[Rule('required|boolean')]
    public bool $is_active = true;

    public function fillFromModel(Category $category): void
    {
        $this->categoryId = $category->id;
        $this->business_unit = $category->business_unit;
        $this->expense_name = $category->expense_name;
        $this->expense_type_id = $category->expense_type_id;
        $this->provider_name = $category->provider_name;
        $this->is_active = (bool) $category->is_active;
    }
}
