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
                                   focus:border-emerald-500 focus:ring-emerald-500
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
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
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
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    @foreach(\App\Domain\BusinessUnit::cases() as $bu)
                        <option value="{{ $bu->value }}">{{ $bu->value }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Periodo --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Periodo</label>
                <input
                    type="month"
                    wire:model.live="period_key"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>

            {{-- Reportes + Mes actual + Limpiar --}}
            <div class="lg:col-span-3 flex items-end justify-end gap-3">
                {{-- Grupo: Reportes --}}
                <div class="flex flex-col items-center gap-1">
                    <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Reportes</span>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            wire:click="exportExcel"
                            wire:loading.attr="disabled"
                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-emerald-600 hover:bg-emerald-50 transition disabled:opacity-50
                                   dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                            aria-label="Exportar Excel"
                        >
                            <svg class="size-[15px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M12 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M21.375 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M12 14.625v-1.5m0 1.5c0 .621.504 1.125 1.125 1.125M12 14.625c0 .621-.504 1.125-1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 0v1.5c0 .621-.504 1.125-1.125 1.125" />
                            </svg>
                            <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0 shadow-sm transition group-hover:opacity-100 dark:bg-black">Excel</span>
                        </button>
                        <button
                            type="button"
                            wire:click="exportPdf"
                            wire:loading.attr="disabled"
                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-rose-600 hover:bg-rose-50 transition disabled:opacity-50
                                   dark:text-rose-300 dark:hover:bg-rose-900/30"
                            aria-label="Exportar PDF"
                        >
                            <svg class="size-[15px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0 shadow-sm transition group-hover:opacity-100 dark:bg-black">PDF</span>
                        </button>
                        <span wire:loading wire:target="exportExcel,exportPdf" class="text-[10px] text-gray-500 dark:text-gray-400">Generando…</span>
                    </div>
                </div>

                {{-- Separador --}}
                <div class="h-8 w-px bg-gray-200 dark:bg-white/10"></div>

                {{-- Mes actual --}}
                <button
                    type="button"
                    wire:click="setCurrentMonth"
                    class="inline-flex items-center justify-center rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                    title="Ir al mes actual"
                >
                    <svg class="mr-2 size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
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
                    <svg class="mr-2 size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3ZM16.5 16.5l5 5m0-5-5 5" /></svg>
                    Limpiar
                </button>
            </div>
        @endif
    </div>

    {{-- CARDS --}}
    <div class="grid gap-3 md:grid-cols-3">
        @foreach($totalsByUnit as $unitTotal)
            <div wire:key="unit-total-{{ $loop->index }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $unitTotal->business_unit }}</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                    $ {{ number_format((float) $unitTotal->total_amount, 2) }}
                </p>
            </div>
        @endforeach
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

                            $buClass = \App\Domain\BusinessUnit::tryFrom($bu)?->badgeClasses()
                                ?? 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-900/30 dark:text-gray-200';

                            $typeName = $supply->category?->expenseType?->expense_type_name ?? '—';
                            $catName  = $supply->category?->expense_name ?? '—';
                            $provName = $supply->category?->provider_name ?? null;
                        @endphp

                        <tr wire:key="supply-{{ $supply->id }}" class="hover:bg-gray-50/60 dark:hover:bg-white/5">
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

                                    {{-- Comprobante --}}
                                    @if($supply->receipt_path)
                                        <button
                                            type="button"
                                            wire:click="showReceipt({{ $supply->id }})"
                                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-amber-600 hover:bg-amber-50
                                                   dark:text-amber-300 dark:hover:bg-amber-900/30 transition"
                                            aria-label="Ver comprobante"
                                        >
                                            <i class="fa-thin fa-image fa-fw text-[14px]"></i>
                                            <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0
                                                       shadow-sm transition group-hover:opacity-100 dark:bg-black">
                                                Ver comprobante
                                            </span>
                                        </button>
                                    @endif

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
                                        class="group relative inline-flex items-center justify-center rounded-md p-2 text-emerald-600 hover:bg-emerald-50
                                               dark:text-emerald-300 dark:hover:bg-emerald-900/30 transition"
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
    <x-modal wire:model="open" maxWidth="2xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $form->supplyId ? 'Editar registro' : 'Nuevo registro' }}
            </h3>
        </div>

        <div class="p-4 space-y-4">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-form-field label="Categoría" name="category_id">
                    <select
                        id="category_id"
                        wire:model.live="category_id"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="">Selecciona…</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">
                                [{{ $cat->business_unit }}] {{ $cat->expenseType?->expense_type_name ?? '—' }} — {{ $cat->expense_name }} @if($cat->provider_name) · {{ $cat->provider_name }} @endif
                            </option>
                        @endforeach
                    </select>
                </x-form-field>

                <x-form-field label="Fecha de pago" name="payment_date">
                    <input
                        id="payment_date"
                        type="date"
                        wire:model.live="payment_date"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                </x-form-field>

                <div>
                    <x-form-field label="Monto" name="amount">
                        <input
                            id="amount"
                            type="number"
                            step="0.01"
                            wire:model.live="amount"
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                   focus:border-emerald-500 focus:ring-emerald-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        >
                    </x-form-field>

                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                        <input type="checkbox" wire:model.live="is_adjustment" class="rounded border-gray-300 dark:border-white/15">
                        Es ajuste (guardar negativo)
                    </label>
                    @error('is_adjustment') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <x-form-field label="Método de pago" name="payment_type">
                    <select
                        id="payment_type"
                        wire:model.live="payment_type"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
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
                </x-form-field>

                <x-form-field label="Estado" name="status">
                    <select
                        id="status"
                        wire:model.live="status"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="pendiente">Pendiente</option>
                        <option value="pagado">Pagado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </x-form-field>

                <div class="sm:col-span-2">
                    <x-form-field label="Notas" name="notes">
                        <textarea
                            id="notes"
                            rows="3"
                            wire:model.live="notes"
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                   focus:border-emerald-500 focus:ring-emerald-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        ></textarea>
                    </x-form-field>
                </div>

                {{-- Comprobante (imagen) --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Comprobante</label>

                    @if($form->existingReceiptPath && !$form->removeReceipt && !$form->receipt)
                        <div class="mt-1 flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-gray-800/40">
                            <i class="fa-thin fa-image text-lg text-gray-400"></i>
                            <span class="flex-1 truncate text-xs text-gray-700 dark:text-gray-200">Comprobante adjunto</span>
                            <button
                                type="button"
                                wire:click="$set('form.removeReceipt', true)"
                                class="rounded-md p-1 text-rose-600 hover:bg-rose-50 transition dark:text-rose-400 dark:hover:bg-rose-900/30"
                                title="Eliminar comprobante"
                            >
                                <i class="fa-thin fa-trash fa-fw text-[13px]"></i>
                            </button>
                        </div>
                    @else
                        <input
                            type="file"
                            wire:model="form.receipt"
                            accept="image/*"
                            class="mt-1 block w-full text-xs text-gray-700 dark:text-gray-200
                                   file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-emerald-700
                                   hover:file:bg-emerald-100
                                   dark:file:bg-emerald-900/30 dark:file:text-emerald-300"
                        >
                        <div wire:loading wire:target="form.receipt" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <i class="fa-thin fa-spinner-third animate-spin mr-1"></i> Subiendo imagen…
                        </div>
                    @endif

                    @if($form->receipt && $form->receipt->isPreviewable())
                        <div class="mt-2">
                            <img src="{{ $form->receipt->temporaryUrl() }}" alt="Vista previa" class="h-24 w-auto rounded-md border border-gray-200 object-cover dark:border-white/10">
                        </div>
                    @endif

                    @error('form.receipt') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Opcional. Imagen del comprobante (máx. 5MB).</p>
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
                class="rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50
                       dark:bg-emerald-500 dark:hover:bg-emerald-400"
            >
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </x-modal>


    {{-- MODAL: DETAIL --}}
    @if($detailSupply)
        <x-modal wire:model="showDetailModal" maxWidth="xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detalle</h3>
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
        </x-modal>
    @endif




    {{-- MODAL: RECEIPT IMAGE --}}
    @if($receiptUrl)
        <x-modal wire:model="showReceiptModal" maxWidth="2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    <i class="fa-thin fa-image mr-2"></i> Comprobante
                </h3>
            </div>

            <div class="flex items-center justify-center p-4">
                <img
                    src="{{ $receiptUrl }}"
                    alt="Comprobante de gasto"
                    class="max-h-[70vh] w-auto rounded-md object-contain"
                >
            </div>

            <div class="flex items-center justify-end border-t border-gray-200 px-4 py-3 dark:border-white/10">
                <button
                    type="button"
                    wire:click="closeReceipt"
                    class="rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                >
                    Cerrar
                </button>
            </div>
        </x-modal>
    @endif

</div>
