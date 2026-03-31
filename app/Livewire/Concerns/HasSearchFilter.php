<?php

namespace App\Livewire\Concerns;

trait HasSearchFilter
{
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
}
