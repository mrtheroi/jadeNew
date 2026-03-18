<div class="space-y-4">

    {{-- HEADER --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Users</h2>

                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        Total: {{ $users->total() }}
                    </span>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Administra usuarios, roles y estado. Usa filtros y búsqueda para encontrar rápido.
                </p>
            </div>

            {{-- ACTIONS --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Nuevo --}}
                <button
                    type="button"
                    wire:click="create"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition
                           dark:bg-indigo-500 dark:hover:bg-indigo-400"
                >
                    <i class="fa-thin fa-user-plus mr-2"></i>
                    Nuevo
                </button>

                {{-- Refresh (opcional) --}}
                <button
                    type="button"
                    wire:click="$refresh"
                    class="inline-flex items-center justify-center rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                    title="Actualizar"
                >
                    <i class="fa-thin fa-rotate-right mr-2"></i>
                    Actualizar
                </button>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="mt-4 grid gap-3 lg:grid-cols-12">
            {{-- Search --}}
            <div class="lg:col-span-6">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Buscar</label>
                <div class="mt-1 flex items-center gap-2">
                    <div class="relative w-full">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <i class="fa-thin fa-magnifying-glass"></i>
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.350ms="search"
                            wire:keydown.escape="$set('search','')"
                            placeholder="Nombre, email..."
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        />
                    </div>

                    @if(trim($this->search ?? '') !== '')
                        <button
                            type="button"
                            wire:click="$set('search','')"
                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition
                                   dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                            aria-label="Limpiar búsqueda"
                        >
                            <i class="fa-thin fa-xmark fa-fw text-[15px]"></i>
                            <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0
                                       shadow-sm transition group-hover:opacity-100 dark:bg-black">
                                Limpiar
                            </span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Role --}}
            <div class="lg:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Rol</label>
                <select
                    wire:model.live="filter_role"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-indigo-500 focus:ring-indigo-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="">Todos</option>
                    {{-- Ajusta a tus roles reales --}}
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="user">User</option>
                </select>
            </div>

            {{-- Status --}}
            <div class="lg:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Estado</label>
                <select
                    wire:model.live="filter_status"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-indigo-500 focus:ring-indigo-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="">Todos</option>
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </div>
        </div>

        {{-- CHIPS: filtros activos --}}
        @php
            $chips = [];
            $s = trim($this->search ?? '');
            if ($s !== '') $chips[] = ['icon' => 'fa-magnifying-glass', 'label' => "Búsqueda: {$s}"];
            if (!empty($this->filter_role)) $chips[] = ['icon' => 'fa-user-shield', 'label' => "Rol: {$this->filter_role}"];
            if (!empty($this->filter_status)) $chips[] = ['icon' => 'fa-toggle-on', 'label' => "Estado: {$this->filter_status}"];
        @endphp

        @if(count($chips) > 0)
            <div class="mt-3 flex flex-wrap items-center gap-2">
                @foreach($chips as $c)
                    <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-[11px] text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <i class="fa-thin {{ $c['icon'] }} text-[12px]"></i>
                        {{ $c['label'] }}
                    </span>
                @endforeach

                {{-- Botón global limpiar: ideal que llame resetFilters() --}}
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-3 py-1 text-[11px] font-semibold text-white hover:bg-black transition
                           dark:bg-white dark:text-gray-900"
                >
                    <i class="fa-thin fa-broom-wide"></i>
                    Limpiar
                </button>
            </div>
        @endif
    </div>


    {{-- TABLE --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between px-4 py-3">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Listado</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Mostrando: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $users->count() }}</span>
            </p>
        </div>

        <div class="relative">
            {{-- Loading overlay --}}
            <div wire:loading.flex wire:target="search,filter_role,filter_status" class="absolute inset-0 z-20 items-center justify-center bg-white/60 dark:bg-black/40 backdrop-blur-sm">
                <div class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm
                            dark:bg-gray-900 dark:text-gray-200 dark:border dark:border-white/10">
                    <i class="fa-thin fa-spinner-third animate-spin"></i>
                    Filtrando…
                </div>
            </div>

            <div class="overflow-x-auto max-h-[70vh]">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-950/60 backdrop-blur">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:pl-6 dark:text-gray-300">
                            Usuario
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Rol
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Estado
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Alta
                        </th>
                        <th class="py-3.5 pr-4 pl-3 text-right sm:pr-6">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                            <td class="py-3 pl-4 pr-3 sm:pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        <i class="fa-thin fa-user"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $u->name ?? '—' }}
                                        </div>
                                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">
                                            {{ $u->email ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-sm">
                                @if($u->hasRole('Super'))
                                    <span
                                        class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-600/20
                                                   dark:bg-purple-900/40 dark:text-purple-300 dark:ring-purple-500/60">
                                            SUPER
                                        </span>
                                @elseif($u->hasRole('Admin'))
                                    <span
                                        class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20
                                               dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-500/60">
                                        ADMIN
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20
                                               dark:bg-blue-900/40 dark:text-blue-300 dark:ring-blue-500/60">
                                        USUARIO
                                    </span>
                                @endif
                            </td>


                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 dark:text-gray-400">
                                @if (is_null($u->deleted_at))
                                    <span
                                        class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/30 dark:text-green-400 dark:ring-green-500/50">
                                        Activo
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-red-400/20 dark:bg-red-800/60 dark:text-red-300 dark:ring-red-500/60">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700 dark:text-gray-200">
                                {{ optional($u->created_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap py-3 pr-4 pl-3 text-right text-sm sm:pr-6">
                                <div class="flex items-center justify-end gap-1">

                                    {{-- Editar --}}
                                    <button
                                        type="button"
                                        wire:click="edit({{ $u->id }})"
                                        class="group relative inline-flex items-center justify-center rounded-md p-2 text-indigo-600 hover:bg-indigo-50
                                               dark:text-indigo-300 dark:hover:bg-indigo-900/30 transition"
                                        aria-label="Editar"
                                    >
                                        <i class="fa-thin fa-pen-to-square fa-fw text-[14px]"></i>
                                        <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0
                                                   shadow-sm transition group-hover:opacity-100 dark:bg-black">
                                            Editar
                                        </span>
                                    </button>

                                    {{-- Eliminar --}}
                                    <button
                                        type="button"
                                        wire:click="deleteConfirmation({{ $u->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="deleteConfirmation,destroy"
                                        class="group relative inline-flex items-center justify-center rounded-md p-2 text-rose-600 hover:bg-rose-50
                                               dark:text-rose-300 dark:hover:bg-rose-900/30 transition disabled:opacity-50"
                                        aria-label="Eliminar"
                                    >
                                        <i class="fa-thin fa-trash fa-fw text-[14px]"></i>
                                        <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0
                                                   shadow-sm transition group-hover:opacity-100 dark:bg-black">
                                            Eliminar
                                        </span>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <i class="fa-thin fa-folder-open text-2xl opacity-70"></i>
                                    <p class="text-sm font-semibold">No hay usuarios con los filtros actuales</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Prueba limpiando filtros o ajustando la búsqueda.
                                    </p>
                                    <div class="mt-2">
                                        <button
                                            type="button"
                                            wire:click="resetFilters"
                                            class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-3 py-1 text-[11px] font-semibold text-white hover:bg-black transition
                                                   dark:bg-white dark:text-gray-900"
                                        >
                                            <i class="fa-thin fa-broom-wide"></i>
                                            Limpiar
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-4 py-3 sm:px-6">
            {{ $users->links() }}
        </div>
    </div>


    {{-- MODAL: CREATE / EDIT --}}
    @if($open)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeModal">
            <div class="w-full max-w-xl rounded-xl bg-white shadow-lg dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $selected_id ? 'Editar usuario' : 'Nuevo usuario' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition
                               dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                        aria-label="Cerrar"
                    >
                        <i class="fa-thin fa-xmark fa-fw"></i>
                    </button>
                </div>

                <div class="p-4 space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Nombre</label>
                            <input
                                type="text"
                                wire:model.live="name"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Email</label>
                            <input
                                type="email"
                                wire:model.live="email"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Rol</label>
                            <select
                                wire:model.live="role"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="user">User</option>
                            </select>
                            @error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Estado</label>
                            <select
                                wire:model.live="status"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Passwords (solo si quieres mostrarlos siempre; si no, lo hacemos condicional) --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Password</label>
                            <input
                                type="password"
                                wire:model.live="password"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                            @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Confirmación</label>
                            <input
                                type="password"
                                wire:model.live="password_confirmation"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-white/10">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                               dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition disabled:opacity-50
                               dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    >
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
