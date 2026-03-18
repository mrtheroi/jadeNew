<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Spatie\Permission\Models\Role;

class UserController extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Url]
    public string $search = '';

    public ?string $filter_role = null;
    public ?string $filter_status = null;

    public bool $open = false;

    // Form
    public ?int $selected_id = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public ?string $role = null;

    // ====== Rules ======
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:60'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->selected_id),
            ],

            'role' => ['required', Rule::exists('roles', 'name')],

            // password requerido solo al crear
            'password' => $this->selected_id
                ? ['nullable', 'string', 'min:8']
                : ['required', 'string', 'min:8'],
        ];
    }

    // ====== UI helpers ======
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filter_role = null;
        $this->filter_status = null;
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->selected_id = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = null;
    }

    // ====== CRUD ======
    public function edit(int $id): void
    {
        $user = User::withTrashed()->with('roles')->findOrFail($id);

        $this->selected_id = $user->id;
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->role = $user->roles->pluck('name')->first();
        $this->password = '';

        $this->open = true;
    }

    /**
     * save() crea o actualiza según selected_id
     * (alineado con tu vista que normalmente llama save)
     */
    public function save(): void
    {
        $data = $this->validate();

        $user = $this->selected_id
            ? User::withTrashed()->findOrFail($this->selected_id)
            : new User();

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        } elseif (!$this->selected_id) {
            // seguridad extra por si algo raro pasa
            $user->password = Hash::make($this->password);
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        $this->closeModal();
        $this->resetForm();

        $this->dispatch('notify', message: $this->selected_id ? 'Usuario actualizado con éxito.' : 'Usuario creado con éxito.', type: 'success');
    }

    public function deleteConfirmation(int $id): void
    {
        $this->dispatch('showConfirmationModal', userId: $id)->to(ConfirmModal::class);
    }

    #[On('deleteConfirmed')]
    public function destroy(int $id): void
    {
        $user = User::withTrashed()->findOrFail($id);

        // soft delete
        if ($user->trashed()) {
            // si ya está borrado, podrías restaurar (opcional)
            // $user->restore();
            // $this->dispatch('notify', message: 'Usuario restaurado con éxito.', type: 'success');
            // return;
        }

        $user->delete();
        $this->dispatch('notify', message: 'Usuario eliminado con éxito.', type: 'success');
    }

    // ====== Render ======
    public function render()
    {
        $users = User::query()
            ->withTrashed()
            ->with('roles')
            ->when(trim($this->search) !== '', function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->when(!empty($this->filter_role), function ($q) {
                $role = $this->filter_role;
                $q->whereHas('roles', fn ($rq) => $rq->where('name', $role));
            })
            ->when(!empty($this->filter_status), function ($q) {
                if ($this->filter_status === 'active') {
                    $q->whereNull('deleted_at');
                }
                if ($this->filter_status === 'trashed') {
                    $q->onlyTrashed();
                }
            })
            ->orderByDesc('id')
            ->paginate(10);

        $roles = Role::query()->orderBy('name')->get();

        return view('livewire.users', compact('users', 'roles'));
    }
}
