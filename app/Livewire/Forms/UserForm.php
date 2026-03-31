<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule as VRule;
use Livewire\Form;

class UserForm extends Form
{
    public ?int $selected_id = null;

    public string $name = '';

    public string $email = '';

    public ?string $role = null;

    public string $password = '';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:60'],
            'email' => [
                'required', 'email', 'max:255',
                VRule::unique('users', 'email')->ignore($this->selected_id),
            ],
            'role' => ['required', VRule::exists('roles', 'name')],
            'password' => $this->selected_id
                ? ['nullable', 'string', 'min:8']
                : ['required', 'string', 'min:8'],
        ];
    }

    public function fillFromModel(User $user): void
    {
        $this->selected_id = $user->id;
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->role = $user->roles->pluck('name')->first();
        $this->password = '';
    }

    public function isEditing(): bool
    {
        return $this->selected_id !== null;
    }
}
