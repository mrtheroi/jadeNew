<div class="space-y-4">

    {{-- HEADER --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Supplies</h2>

                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        {{ $businessUnit }} · {{ $periodKey }}
                    </span>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Rango: <span class="font-medium">{{ $from_date }}</span> → <span class="font-medium">{{ $to_date }}</span>
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
                    <i class="fa-thin fa-plus mr-2"></i>
                    Nuevo
                </button>

                {{-- Ingreso mensual --}}
                <button
                    type="button"
                    wire:click="openIncome"
                    class="inline-flex items-center justify-center rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                    aria-label="Ingreso mensual"
                    title="Ingreso mensual"
                >
                    <i class="fa-thin fa-sack-dollar mr-2"></i>
                    Ingreso
                </button>

                {{-- Export Excel (icon) --}}
                <button
                    type="button"
                    wire:click="exportExcel"
                    wire:loading.attr="disabled"
                    class="group relative inline-flex items-center justify-center rounded-md p-2 text-emerald-600 hover:bg-emerald-50 transition disabled:opacity-50
                           dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                    aria-label="Exportar Excel"
                >
                    <i class="fa-thin fa-file-excel fa-fw text-[15px]"></i>
                    <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0
                               shadow-sm transition group-hover:opacity-100 dark:bg-black">
                        Exportar Excel
                    </span>
                </button>

                {{-- Export PDF (icon) --}}
                <button
                    type="button"
                    wire:click="exportPdf"
                    wire:loading.attr="disabled"
                    class="group relative inline-flex items-center justify-center rounded-md p-2 text-rose-600 hover:bg-rose-50 transition disabled:opacity-50
                           dark:text-rose-300 dark:hover:bg-rose-900/30"
                    aria-label="Exportar PDF"
                >
                    <i class="fa-thin fa-file-pdf fa-fw text-[15px]"></i>
                    <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0
                               shadow-sm transition group-hover:opacity-100 dark:bg-black">
                        Exportar PDF
                    </span>
                </button>

                <span wire:loading wire:target="exportExcel,exportPdf" class="text-xs text-gray-500 dark:text-gray-400">
                    Generando…
                </span>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="mt-4 grid gap-3 lg:grid-cols-12">
            {{-- Search --}}
            <div class="lg:col-span-5">
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
                            placeholder="Categoría, proveedor, tipo, estado, método…"
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        />
                    </div>

                    {{-- ✅ FIX: este botón debe limpiar búsqueda, NO tocar periodo --}}
                    @if(trim($this->search) !== '')
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

            {{-- Unidad --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Unidad</label>
                <select
                    wire:model.live="business_unit"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-indigo-500 focus:ring-indigo-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="Jade">Jade</option>
                    <option value="Fuego Ambar">Fuego Ambar</option>
                    <option value="KIN">KIN</option>
                </select>
            </div>

            {{-- Periodo --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Periodo</label>
                <input
                    type="month"
                    wire:model.live="period_key"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                           focus:border-indigo-500 focus:ring-indigo-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>

            {{-- Quick --}}
            <div class="lg:col-span-3 flex items-end justify-start gap-2">
                <button
                    type="button"
                    wire:click="setCurrentMonth"
                    class="inline-flex items-center justify-center rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                    title="Ir al mes actual"
                >
                    <i class="fa-thin fa-calendar-days mr-2"></i>
                    Mes actual
                </button>
            </div>
        </div>

        {{-- CHIPS: filtros activos --}}
        @php
            $chips = [];
            if (trim($this->search) !== '') $chips[] = ['icon' => 'fa-magnifying-glass', 'label' => "Búsqueda: {$this->search}"];
            if ($businessUnit) $chips[] = ['icon' => 'fa-building', 'label' => "Unidad: {$businessUnit}"];
            if ($periodKey) $chips[] = ['icon' => 'fa-calendar', 'label' => "Periodo: {$periodKey}"];
        @endphp

        @if(count($chips) > 0)
            <div class="mt-3 flex flex-wrap items-center gap-2">
                @foreach($chips as $c)
                    <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-[11px] text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <i class="fa-thin {{ $c['icon'] }} text-[12px]"></i>
                        {{ $c['label'] }}
                    </span>
                @endforeach

                {{-- ✅ FIX: limpiar debe llamar método Livewire --}}
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

    {{-- CARDS --}}
    <div class="grid gap-3 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Ingreso mensual</p>
                <i class="fa-thin fa-sack-dollar text-gray-400"></i>
            </div>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                $ {{ number_format((float)($incomePeriod?->income_amount ?? 0), 2) }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $incomePeriod ? 'Registrado' : 'Sin registro' }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900 md:col-span-2">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total de gastos (unidad seleccionada)</p>
                <i class="fa-thin fa-chart-simple text-gray-400"></i>
            </div>

            @php
                $totalUnit = (float) optional($totalsByUnit->firstWhere('business_unit', $businessUnit))->total_amount;
                $incomeAmt = (float) ($incomePeriod?->income_amount ?? 0);
                $pct = ($incomeAmt > 0) ? ($totalUnit / $incomeAmt) : null;
            @endphp

            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                $ {{ number_format($totalUnit, 2) }}
            </p>

            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                @if($pct !== null)
                    Representa <span class="font-medium">{{ number_format($pct * 100, 1) }}%</span> del ingreso.
                @else
                    Agrega ingreso para calcular porcentaje.
                @endif
            </p>
        </div>
    </div>

    {{-- TABLE WRAPPER (overlay + sticky header) --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between px-4 py-3">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Registros</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Total: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $supplies->total() }}</span>
            </p>
        </div>

        <div class="relative">
            {{-- Loading overlay --}}
            <div wire:loading.flex wire:target="search,business_unit,period_key" class="absolute inset-0 z-20 items-center justify-center bg-white/60 dark:bg-black/40 backdrop-blur-sm">
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
                            Fecha
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Unidad
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Tipo / Categoría
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Método
                        </th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Estado
                        </th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            Monto
                        </th>
                        <th class="py-3.5 pr-4 pl-3 text-right sm:pr-6">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($supplies as $supply)
                        @php
                            $bu = $supply->category?->business_unit ?? '—';
                            $amount = (float) $supply->amount;
                            $isAdj = $amount < 0;

                            $status = $supply->status ?? '—';
                            $statusClass = match($status) {
                                'pagado' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300',
                                'pendiente' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300',
                                'cancelado' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-900/30 dark:text-rose-300',
                                default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-900/30 dark:text-gray-200',
                            };

                            $buClass = match($bu) {
                                'Jade' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300',
                                'Fuego Ambar' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300',
                                'KIN' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-900/30 dark:text-indigo-300',
                                default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-900/30 dark:text-gray-200',
                            };

                            $typeName = $supply->category?->expenseType?->expense_type_name ?? '—';
                            $catName  = $supply->category?->expense_name ?? '—';
                            $provName = $supply->category?->provider_name ?? null;
                        @endphp

                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                            <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm text-gray-900 sm:pl-6 dark:text-white">
                                {{ optional($supply->payment_date)->format('Y-m-d') ?? '—' }}
                                @if($isAdj)
                                    <span class="ml-2 inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        Ajuste
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-sm">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $buClass }}">
                                    {{ $bu }}
                                </span>
                            </td>

                            <td class="px-3 py-3 text-sm text-gray-900 dark:text-white">
                                <div class="font-medium truncate max-w-[340px]" title="{{ $typeName }}">
                                    {{ $typeName }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[340px]" title="{{ $provName ? ($catName.' · '.$provName) : $catName }}">
                                    {{ $catName }}
                                    @if($provName) · {{ $provName }} @endif
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700 dark:text-gray-200">
                                {{ $supply->payment_type ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-sm">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-right text-sm font-semibold {{ $amount < 0 ? 'text-rose-600 dark:text-rose-300' : 'text-gray-900 dark:text-white' }}">
                                $ {{ number_format($amount, 2) }}
                            </td>

                            <td class="whitespace-nowrap py-3 pr-4 pl-3 text-right text-sm sm:pr-6">
                                <div class="flex items-center justify-end gap-1">

                                    {{-- Ver --}}
                                    <button
                                        type="button"
                                        wire:click="showDetail({{ $supply->id }})"
                                        class="group relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900
                                               dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition"
                                        aria-label="Ver detalle"
                                    >
                                        <i class="fa-thin fa-eye fa-fw text-[14px]"></i>
                                        <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0
                                                   shadow-sm transition group-hover:opacity-100 dark:bg-black">
                                            Ver detalle
                                        </span>
                                    </button>

                                    {{-- Editar --}}
                                    <button
                                        type="button"
                                        wire:click="edit({{ $supply->id }})"
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
                                        wire:click="deleteConfirmation({{ $supply->id }})"
                                        wire:loading.attr="disabled"
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
                            <td colspan="7" class="py-10 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <i class="fa-thin fa-folder-open text-2xl opacity-70"></i>
                                    <p class="text-sm font-semibold">No hay registros con los filtros actuales</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Prueba cambiando el periodo o limpiando la búsqueda.
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="setCurrentMonth"
                                            class="rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold hover:bg-gray-50 transition
                                                   dark:border-white/10 dark:hover:bg-white/5"
                                        >
                                            Mes actual
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="$set('search','')"
                                            class="rounded-md bg-gray-100 px-3 py-2 text-xs font-semibold hover:bg-gray-200 transition
                                                   dark:bg-gray-800 dark:hover:bg-gray-700"
                                        >
                                            Limpiar búsqueda
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
            {{ $supplies->links() }}
        </div>

        @livewire('confirm-modal')
    </div>


    {{-- MODAL: CREATE / EDIT --}}
    @if($open)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeModal">
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-lg dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $supplyId ? 'Editar registro' : 'Nuevo registro' }}
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
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Categoría</label>
                            <select
                                wire:model.live="category_id"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">Selecciona…</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        [{{ $cat->business_unit }}] {{ $cat->expenseType?->expense_type_name ?? '—' }} — {{ $cat->expense_name }} @if($cat->provider_name) · {{ $cat->provider_name }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Fecha de pago</label>
                            <input
                                type="date"
                                wire:model.live="payment_date"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                            @error('payment_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Monto</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model.live="amount"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                            @error('amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

                            <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                <input type="checkbox" wire:model.live="is_adjustment" class="rounded border-gray-300 dark:border-white/15">
                                Es ajuste (guardar negativo)
                            </label>
                            @error('is_adjustment') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Método de pago</label>
                            <select
                                wire:model.live="payment_type"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">—</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta_credito">Tarjeta crédito</option>
                                <option value="tarjeta_debito">Tarjeta débito</option>
                                <option value="cheque">Cheque</option>
                                <option value="otro">Otro</option>
                            </select>
                            @error('payment_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Estado</label>
                            <select
                                wire:model.live="status"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Notas</label>
                            <textarea
                                rows="3"
                                wire:model.live="notes"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            ></textarea>
                            @error('notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
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


    {{-- MODAL: DETAIL --}}
    @if($showDetailModal && $detailSupply)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeDetail">
            <div class="w-full max-w-xl rounded-xl bg-white shadow-lg dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detalle</h3>
                    <button
                        type="button"
                        wire:click="closeDetail"
                        class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition
                               dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                        aria-label="Cerrar"
                    >
                        <i class="fa-thin fa-xmark fa-fw"></i>
                    </button>
                </div>

                <div class="p-4 space-y-3 text-sm text-gray-800 dark:text-gray-100">
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Unidad</div>
                            <div class="font-semibold">{{ $detailSupply->category?->business_unit ?? '—' }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Fecha</div>
                            <div class="font-semibold">{{ optional($detailSupply->payment_date)->format('Y-m-d') ?? '—' }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40 col-span-2">
                            <div class="text-gray-500 dark:text-gray-400">Categoría</div>
                            <div class="font-semibold">
                                {{ $detailSupply->category?->expenseType?->expense_type_name ?? '—' }} — {{ $detailSupply->category?->expense_name ?? '—' }}
                            </div>
                            <div class="text-gray-500 dark:text-gray-400">
                                {{ $detailSupply->category?->provider_name ?? '' }}
                            </div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Método</div>
                            <div class="font-semibold">{{ $detailSupply->payment_type ?? '—' }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Estado</div>
                            <div class="font-semibold">{{ ucfirst($detailSupply->status ?? '—') }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40 col-span-2">
                            <div class="text-gray-500 dark:text-gray-400">Monto</div>
                            <div class="text-base font-bold {{ (float)$detailSupply->amount < 0 ? 'text-rose-600 dark:text-rose-300' : '' }}">
                                $ {{ number_format((float)$detailSupply->amount, 2) }}
                            </div>
                        </div>
                        @if($detailSupply->notes)
                            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40 col-span-2">
                                <div class="text-gray-500 dark:text-gray-400">Notas</div>
                                <div class="text-sm">{{ $detailSupply->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end border-t border-gray-200 px-4 py-3 dark:border-white/10">
                    <button
                        type="button"
                        wire:click="closeDetail"
                        class="rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                               dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- MODAL: INCOME --}}
    @if($openIncomeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeIncome">
            <div class="w-full max-w-xl rounded-xl bg-white shadow-lg dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Ingreso mensual · {{ $businessUnit }} · {{ $periodKey }}
                    </h3>
                    <button
                        type="button"
                        wire:click="closeIncome"
                        class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition
                               dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                        aria-label="Cerrar"
                    >
                        <i class="fa-thin fa-xmark fa-fw"></i>
                    </button>
                </div>

                <div class="p-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Monto</label>
                        <input
                            type="number"
                            step="0.01"
                            wire:model.live="income_amount"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        >
                        @error('income_amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Notas</label>
                        <textarea
                            rows="3"
                            wire:model.live="income_notes"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        ></textarea>
                        @error('income_notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-white/10">
                    <button
                        type="button"
                        wire:click="closeIncome"
                        class="rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                               dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="saveIncome"
                        wire:loading.attr="disabled"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition disabled:opacity-50
                               dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    >
                        <span wire:loading.remove wire:target="saveIncome">Guardar</span>
                        <span wire:loading wire:target="saveIncome">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
