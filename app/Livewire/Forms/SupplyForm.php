<?php

namespace App\Livewire\Forms;

use App\Models\Supply;
use Livewire\Attributes\Rule;
use Livewire\Form;

class SupplyForm extends Form
{
    public ?int $supplyId = null;

    #[Rule('required|exists:categories,id')]
    public string $category_id = '';

    #[Rule('required|numeric|min:0')]
    public string $amount = '';

    #[Rule('boolean')]
    public bool $is_adjustment = false;

    #[Rule('nullable|in:efectivo,transferencia,tarjeta_credito,tarjeta_debito,cheque,otro')]
    public ?string $payment_type = null;

    #[Rule('nullable|date')]
    public ?string $payment_date = null;

    #[Rule('required|in:pendiente,pagado,cancelado')]
    public string $status = 'pendiente';

    #[Rule('nullable|string|max:1000')]
    public ?string $notes = null;

    #[Rule('nullable|image|max:5120')]
    public $receipt = null;

    public ?string $existingReceiptPath = null;

    public bool $removeReceipt = false;

    public function fillFromModel(Supply $supply): void
    {
        $this->supplyId = $supply->id;
        $this->category_id = (string) $supply->category_id;
        $this->is_adjustment = ((float) $supply->amount) < 0;
        $this->amount = (string) abs((float) $supply->amount);
        $this->payment_type = $supply->payment_type;
        $this->payment_date = $supply->payment_date?->format('Y-m-d');
        $this->status = $supply->status;
        $this->notes = $supply->notes;
        $this->existingReceiptPath = $supply->receipt_path;
        $this->receipt = null;
        $this->removeReceipt = false;
    }

    /**
     * Retorna el monto final: negativo si es ajuste, positivo si no.
     */
    public function resolvedAmount(): float
    {
        $amount = (float) $this->amount;

        if ($this->is_adjustment && $amount > 0) {
            return -1 * $amount;
        }

        if (! $this->is_adjustment && $amount < 0) {
            return abs($amount);
        }

        return $amount;
    }
}
