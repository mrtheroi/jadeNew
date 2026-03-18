<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class Notification extends Component
{
    public bool $visible = false;
    public string $message = '';
    public string $type = 'success'; // success, error, warning

    #[On('notify')]
    public function showNotification(string $message, string $type = 'success')
    {
        Log::info('llego la notificación');
        $this->message = $message;
        $this->type = $type;
        $this->visible = true;
    }

    public function render()
    {
        return view('livewire.modals.notification');
    }
}
