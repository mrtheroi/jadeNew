<?php

namespace App\Livewire\Forms;

use App\Models\IncomePeriod;
use Livewire\Attributes\Rule;
use Livewire\Form;

class IncomeForm extends Form
{
    #[Rule('required|numeric|min:0')]
    public ?float $income_amount = null;

    #[Rule('nullable|string|max:1000')]
    public ?string $income_notes = null;

    public function fillFromModel(IncomePeriod $income): void
    {
        $this->income_amount = (float) $income->income_amount;
        $this->income_notes = $income->notes;
    }
}
