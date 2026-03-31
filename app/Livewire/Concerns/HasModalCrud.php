<?php

namespace App\Livewire\Concerns;

trait HasModalCrud
{
    public bool $open = false;

    public function create(): void
    {
        $this->form->reset();
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
    }
}
