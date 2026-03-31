<?php

namespace App\Livewire\Forms;

use App\Models\ExpenseType;
use Illuminate\Validation\Rule as VRule;
use Livewire\Form;

class ExpenseTypeForm extends Form
{
    public ?int $expenseTypeId = null;

    public string $expense_type_name = '';

    public bool $is_active = true;

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

    public function fillFromModel(ExpenseType $expenseType): void
    {
        $this->expenseTypeId = $expenseType->id;
        $this->expense_type_name = $expenseType->expense_type_name;
        $this->is_active = (bool) $expenseType->is_active;
    }
}
