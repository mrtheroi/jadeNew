<div class="space-y-4">

    {{-- HEADER / FILTER CARD --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                        Tipos de gasto
                    </h2>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-2xl">
                    Administra el catálogo de tipos de gasto para clasificar categorías y reportes.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="create"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition
                           dark:bg-indigo-500 dark:hover:bg-indigo-400"
                >
                    <i class="fa-thin fa-plus mr-2"></i>
                    Nuevo tipo
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
                            wire:model.live.debounce.400ms="search"
                            wire:keydown.escape="$set('search','')"
                            placeholder="Buscar por nombre…"
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        />
                    </div>

                    @if(trim($this->search) !== '')
                        <button
                            type="button"
                            wire:click="$set('search','')"
                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition
                                   dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                            aria-label="Limpiar búsqueda"
                            title="Limpiar búsqueda"
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

            {{-- Stats --}}
            <div class="lg:col-span-6 flex items-end justify-end">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Total:
                    <span class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ $expenseTypes->total() }}
                    </span>
                </p>
            </div>
        </div>

        {{-- CHIPS (solo búsqueda) --}}
        @if(trim($this->search) !== '')
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-[11px] text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <i class="fa-thin fa-magnifying-glass text-[12px]"></i>
                    Búsqueda: {{ $this->search }}
                </span>
            </div>
        @endif
    </div>


    {{-- TABLE WRAPPER --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between px-4 py-3">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Listado</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Mostrando:
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $expenseTypes->count() }}</span>
            </p>
        </div>

        <div class="relative">

            {{-- Loading overlay --}}
            <div wire:loading.flex wire:target="search" class="absolute inset-0 z-20 items-center justify-center bg-white/60 dark:bg-black/40 backdrop-blur-sm">
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
                            Nombre
                        </th>

                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Estado
                        </th>

                        <th class="py-3.5 pr-4 pl-3 text-right sm:pr-6">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($expenseTypes as $et)
                        @php
                            $active = (bool) $et->is_active;

                            $statusClass = $active
                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300'
                                : 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-900/30 dark:text-rose-300';
                        @endphp

                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">

                            {{-- Nombre --}}
                            <td class="py-3 pl-4 pr-3 text-sm text-gray-900 sm:pl-6 dark:text-white">
                                <div class="font-medium truncate max-w-[520px]" title="{{ $et->expense_type_name }}">
                                    {{ $et->expense_type_name ?? '—' }}
                                </div>
                            </td>

                            {{-- Estado --}}
                            <td class="whitespace-nowrap px-3 py-3 text-sm">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                    {{ $active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td class="whitespace-nowrap py-3 pr-4 pl-3 text-right text-sm sm:pr-6">
                                <div class="flex items-center justify-end gap-1">

                                    {{-- Editar --}}
                                    <button
                                        type="button"
                                        wire:click="edit({{ $et->id }})"
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
                                        wire:click="deleteConfirmation({{ $et->id }})"
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
                            <td colspan="3" class="py-10 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <i class="fa-thin fa-folder-open text-2xl opacity-70"></i>
                                    <p class="text-sm font-semibold">No hay tipos de gasto con los filtros actuales</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Prueba limpiando la búsqueda o creando un nuevo tipo.
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="$set('search','')"
                                            class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-3 py-1 text-[11px] font-semibold text-white hover:bg-black transition
                                                   dark:bg-white dark:text-gray-900"
                                        >
                                            <i class="fa-thin fa-broom-wide"></i>
                                            Limpiar búsqueda
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="create"
                                            class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-3 py-1 text-[11px] font-semibold text-white hover:bg-indigo-500 transition
                                                   dark:bg-indigo-500 dark:hover:bg-indigo-400"
                                        >
                                            <i class="fa-thin fa-plus"></i>
                                            Nuevo tipo
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
            {{ $expenseTypes->onEachSide(1)->links() }}
        </div>
    </div>

    {{-- MODALS --}}
    @include('livewire.modals.expense-type-form')
    @livewire('confirm-modal')
</div>
