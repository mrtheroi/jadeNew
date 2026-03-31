<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasModalCrud;
use App\Livewire\Concerns\HasSearchFilter;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserController extends Component
{
    use HasModalCrud, HasSearchFilter, WithoutUrlPagination, WithPagination;

    #[Url]
    public string $search = '';

    public ?string $filter_role = null;

    public ?string $filter_status = null;

    public UserForm $form;

    // ====== UI helpers ======
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

    // ====== CRUD ======
    public function edit(int $id): void
    {
        $user = User::withTrashed()->with('roles')->findOrFail($id);

        $this->form->fillFromModel($user);
        $this->open = true;
    }

    public function save(): void
    {
        $data = $this->form->validate();

        $user = $this->form->isEditing()
            ? User::withTrashed()->findOrFail($this->form->selected_id)
            : new User;

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        } elseif (! $this->form->isEditing()) {
            $user->password = Hash::make($this->form->password);
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        $message = $this->form->isEditing() ? 'Usuario actualizado con éxito.' : 'Usuario creado con éxito.';
        $this->closeModal();
        $this->form->reset();
        $this->dispatch('notify', message: $message, type: 'success');
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
            ->when(! empty($this->filter_role), function ($q) {
                $role = $this->filter_role;
                $q->whereHas('roles', fn ($rq) => $rq->where('name', $role));
            })
            ->when(! empty($this->filter_status), function ($q) {
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
