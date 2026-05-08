<div class="space-y-4">

    {{-- HEADER --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Empleados</h2>
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        Recursos Humanos
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Directorio de empleados con datos personales, laborales y contacto de emergencia.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    wire:click="create"
                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500 transition
                           dark:bg-emerald-500 dark:hover:bg-emerald-400"
                >
                    <svg class="mr-2 size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo
                </button>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="mt-4 grid gap-3 lg:grid-cols-12">
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
                            placeholder="Nombre, email, número de empleado o CURP…"
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-xs text-gray-900 shadow-sm
                                   focus:border-emerald-500 focus:ring-emerald-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        />
                    </div>

                    @if(trim($this->search) !== '')
                        <button
                            type="button"
                            wire:click="$set('search','')"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition
                                   dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                            aria-label="Limpiar búsqueda"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Unidad</label>
                <select
                    wire:model.live="business_unit"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="">Todas las unidades</option>
                    @foreach(\App\Domain\BusinessUnit::cases() as $bu)
                        <option value="{{ $bu->value }}">{{ $bu->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Estado</label>
                <select
                    wire:model.live="status"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="active">Solo activos</option>
                    <option value="inactive">Solo inactivos</option>
                    <option value="all">Todos</option>
                </select>
            </div>
        </div>

        {{-- CHIPS --}}
        @php
            $chips = [];
            if (trim($this->search) !== '') $chips[] = ['icon' => 'fa-magnifying-glass', 'label' => "Búsqueda: {$this->search}"];
            if ($business_unit) $chips[] = ['icon' => 'fa-building', 'label' => "Unidad: {$business_unit}"];
            $statusLabel = match($status) {
                'active' => 'Solo activos',
                'inactive' => 'Solo inactivos',
                default => 'Todos',
            };
            if ($status !== 'active') $chips[] = ['icon' => 'fa-filter', 'label' => "Estado: {$statusLabel}"];
        @endphp

        @if(count($chips) > 0)
            <div class="mt-3 flex flex-wrap items-center gap-2">
                @foreach($chips as $c)
                    <span wire:key="chip-{{ $loop->index }}" class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-[11px] text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <i class="fa-thin {{ $c['icon'] }} text-[12px]"></i>
                        {{ $c['label'] }}
                    </span>
                @endforeach

                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center justify-center rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                >
                    Limpiar
                </button>
            </div>
        @endif
    </div>

    {{-- CARDS --}}
    <div class="space-y-3">
        {{-- TOTAL ACTIVOS --}}
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-900/10">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                        Empleados activos
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Personal en operación bajo el filtro vigente.
                    </p>
                </div>
                <p class="text-2xl font-semibold text-emerald-700 dark:text-emerald-300">
                    {{ $totalActive }}
                </p>
            </div>
        </div>

        {{-- BREAKDOWN POR UNIDAD --}}
        @if($totalsByUnit->isNotEmpty())
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($totalsByUnit as $row)
                    <div wire:key="unit-total-{{ $row->business_unit }}"
                         class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ $row->business_unit }}
                        </p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $row->total }} {{ $row->total === 1 ? 'empleado' : 'empleados' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- INACTIVOS (card aparte, solo si hay) --}}
        @if($totalInactive > 0 && $status !== 'inactive')
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 shadow-sm dark:border-white/10 dark:bg-gray-800/50">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-thin fa-user-slash text-gray-500 dark:text-gray-400"></i>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                Empleados inactivos
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Dados de baja. No suman al total activo. Filtrá por «Solo inactivos» para verlos.
                            </p>
                        </div>
                    </div>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                        {{ $totalInactive }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- TABLE --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between px-4 py-3">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Listado</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Total: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $employees->total() }}</span>
            </p>
        </div>

        <div class="relative">
            <div wire:loading.flex wire:target="search,business_unit,status" class="absolute inset-0 z-20 items-center justify-center bg-white/60 dark:bg-black/40 backdrop-blur-sm">
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
                            Empleado
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Unidad
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Departamento
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Contacto
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Ingreso
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Estado
                        </th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
                        @forelse($employees as $employee)
                            <tr wire:key="employee-{{ $employee->id }}" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                <td class="whitespace-nowrap py-3.5 pl-4 pr-3 text-xs sm:pl-6">
                                    <div class="flex flex-col">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="font-mono text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ $employee->employee_number }}
                                            </span>
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $employee->full_name }}
                                        </span>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-gray-700 dark:text-gray-200">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ $employee->business_unit }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                                    {{ $employee->department ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                                    <div class="flex flex-col">
                                        <span class="text-gray-700 dark:text-gray-200">{{ $employee->email ?? '—' }}</span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $employee->phone ?? '' }}</span>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-gray-700 dark:text-gray-300">
                                    {{ $employee->hired_at?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-3.5 text-xs">
                                    @if($employee->is_active)
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-gray-100 text-gray-700 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-300">
                                            Baja {{ $employee->terminated_at?->format('Y-m-d') ? '· '.$employee->terminated_at->format('Y-m-d') : '' }}
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap py-3.5 pl-3 pr-4 text-right text-xs font-medium sm:pr-6">
                                    <div class="inline-flex items-center gap-1">
                                        <button
                                            type="button"
                                            wire:click="showDetail({{ $employee->id }})"
                                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition
                                                   dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                                            aria-label="Ver detalle"
                                        >
                                            <i class="fa-thin fa-eye"></i>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="edit({{ $employee->id }})"
                                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-indigo-600 hover:bg-indigo-50 transition
                                                   dark:text-indigo-300 dark:hover:bg-indigo-900/30"
                                            aria-label="Editar"
                                        >
                                            <i class="fa-thin fa-pen"></i>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="deleteConfirmation({{ $employee->id }})"
                                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-rose-600 hover:bg-rose-50 transition
                                                   dark:text-rose-300 dark:hover:bg-rose-900/30"
                                            aria-label="Eliminar"
                                        >
                                            <i class="fa-thin fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                                    Sin empleados para los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-4 py-3">
            {{ $employees->links() }}
        </div>
    </div>

    {{-- MODAL DETALLE --}}
    @if($showDetailModal && $detailEmployee)
        <x-modal wire:model="showDetailModal" maxWidth="2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $detailEmployee->full_name }}
                    <span class="ml-2 text-xs font-normal text-gray-500 dark:text-gray-400">
                        {{ $detailEmployee->employee_number }}
                    </span>
                </h3>
                <button type="button" wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-4 space-y-4 text-xs text-gray-700 dark:text-gray-200">
                {{-- IDENTIFICACIÓN --}}
                <div class="grid grid-cols-2 gap-3">
                    <div><span class="text-gray-500 dark:text-gray-400">Email:</span> {{ $detailEmployee->email ?? '—' }}</div>
                    <div><span class="text-gray-500 dark:text-gray-400">Teléfono:</span> {{ $detailEmployee->phone ?? '—' }}</div>
                    <div><span class="text-gray-500 dark:text-gray-400">CURP:</span> {{ $detailEmployee->curp ?? '—' }}</div>
                    <div><span class="text-gray-500 dark:text-gray-400">Edad:</span> {{ $detailEmployee->age ? $detailEmployee->age.' años' : '—' }}</div>
                </div>

                <div class="border-t border-gray-200 pt-3 dark:border-white/10">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Personales</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div><span class="text-gray-500 dark:text-gray-400">Nacimiento:</span> {{ $detailEmployee->birth_date?->format('Y-m-d') ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Lugar:</span> {{ $detailEmployee->birth_place ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Género:</span> {{ $detailEmployee->gender ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Estado civil:</span> {{ $detailEmployee->marital_status ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Nacionalidad:</span> {{ $detailEmployee->nationality }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Hijos:</span> {{ $detailEmployee->children_count }}</div>
                        <div class="col-span-2"><span class="text-gray-500 dark:text-gray-400">Dirección:</span> {{ $detailEmployee->address ?? '—' }}</div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-3 dark:border-white/10">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Laborales</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div><span class="text-gray-500 dark:text-gray-400">Unidad:</span> {{ $detailEmployee->business_unit }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Departamento:</span> {{ $detailEmployee->department ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Gerente:</span> {{ $detailEmployee->manager_name ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Ingreso:</span> {{ $detailEmployee->hired_at?->format('Y-m-d') ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Estado:</span> {{ $detailEmployee->is_active ? 'Activo' : 'Baja' }}</div>
                        @if(! $detailEmployee->is_active)
                            <div><span class="text-gray-500 dark:text-gray-400">Baja:</span> {{ $detailEmployee->terminated_at?->format('Y-m-d') ?? '—' }}</div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-3 dark:border-white/10">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Contacto de emergencia</h4>
                    <div class="grid grid-cols-3 gap-3">
                        <div><span class="text-gray-500 dark:text-gray-400">Nombre:</span> {{ $detailEmployee->emergency_contact_name ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Teléfono:</span> {{ $detailEmployee->emergency_contact_phone ?? '—' }}</div>
                        <div><span class="text-gray-500 dark:text-gray-400">Parentesco:</span> {{ $detailEmployee->emergency_contact_relationship ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-white/10">
                <button
                    type="button"
                    wire:click="closeDetail"
                    class="rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                >
                    Cerrar
                </button>
            </div>
        </x-modal>
    @endif

    {{-- MODAL FORM --}}
    @include('livewire.modals.form-employee')

    <livewire:confirm-modal />
    <livewire:notification />
</div>
