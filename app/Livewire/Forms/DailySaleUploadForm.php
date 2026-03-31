<?php

namespace App\Livewire\Forms;

use App\Domain\BusinessUnit;
use App\Models\DailySale;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Rule;
use Livewire\Form;

class DailySaleUploadForm extends Form
{
    #[Rule(['required', new Enum(BusinessUnit::class)])]
    public string $business_unit = BusinessUnit::Jade->value;

    #[Rule('required|date')]
    public ?string $operation_date = null;

    #[Rule('required|in:1,2')]
    public int $turno = 1;

    #[Rule('required|file|mimes:pdf|max:10240')]
    public $file = null;

    public function fillForRetry(DailySale $sale): void
    {
        $this->business_unit = $sale->business_unit;
        $this->operation_date = $sale->operation_date->format('Y-m-d');
        $this->turno = $sale->turno;
        $this->file = null;
    }
}
