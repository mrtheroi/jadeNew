<div class="space-y-4">

    {{-- HEADER --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Órdenes de Compra</h2>
                    <span class="inline-flex items-center rounded-md bg-indigo-100 px-2 py-1 text-[11px] font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                        Compras y Pagos
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    OCs generadas postmortem por día y unidad. Cada OC consolida las compras de su jornada.
                </p>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="mt-4 grid gap-3 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Buscar</label>
                <div class="mt-1 relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="fa-thin fa-magnifying-glass"></i>
                    </span>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="search"
                        wire:keydown.escape="$set('search','')"
                        placeholder="Número de OC o notas…"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Unidad</label>
                <select
                    wire:model.live="business_unit"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="">Todas</option>
                    @foreach(\App\Domain\BusinessUnit::cases() as $bu)
                        <option value="{{ $bu->value }}">{{ $bu->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Estado</label>
                <select
                    wire:model.live="status"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="closed">Cerradas</option>
                    <option value="cancelled">Anuladas</option>
                    <option value="all">Todas</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Desde</label>
                <input
                    type="date"
                    wire:model.live="date_from"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Hasta</label>
                <input
                    type="date"
                    wire:model.live="date_to"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>
        </div>

        {{-- CHIPS --}}
        @php
            $chips = [];
            if (trim($this->search) !== '') $chips[] = "Búsqueda: {$this->search}";
            if ($business_unit) $chips[] = "Unidad: {$business_unit}";
            if ($status !== 'closed') $chips[] = 'Estado: '.($status === 'cancelled' ? 'Anuladas' : 'Todas');
            if ($date_from) $chips[] = "Desde: {$date_from}";
            if ($date_to) $chips[] = "Hasta: {$date_to}";
        @endphp

        @if(count($chips) > 0)
            <div class="mt-3 flex flex-wrap items-center gap-2">
                @foreach($chips as $c)
                    <span wire:key="chip-{{ $loop->index }}" class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-[11px] text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        {{ $c }}
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
        <div class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-900/10">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-indigo-700 dark:text-indigo-300">
                        Total facturado en OCs
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $totalClosed }} {{ $totalClosed === 1 ? 'OC cerrada' : 'OCs cerradas' }} bajo el filtro vigente.
                    </p>
                </div>
                <p class="text-2xl font-semibold text-indigo-700 dark:text-indigo-300">
                    $ {{ number_format($totalAmount, 2) }}
                </p>
            </div>
        </div>

        @if($totalsByUnit->isNotEmpty())
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($totalsByUnit as $row)
                    <div wire:key="oc-unit-{{ $row->business_unit }}"
                         class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ $row->business_unit }}
                        </p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $row->count }} {{ (int) $row->count === 1 ? 'OC' : 'OCs' }} · $ {{ number_format((float) $row->total_amount, 2) }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        @if($totalCancelled > 0 && $status !== 'cancelled')
            <div class="rounded-xl border border-rose-200 bg-rose-50/40 p-4 shadow-sm dark:border-rose-500/20 dark:bg-rose-900/10">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-thin fa-ban text-rose-600 dark:text-rose-300"></i>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-rose-700 dark:text-rose-300">
                                OCs anuladas
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Las compras de OCs anuladas quedaron liberadas. Filtrá «Anuladas» para verlas.
                            </p>
                        </div>
                    </div>
                    <p class="text-lg font-semibold text-rose-700 dark:text-rose-300">
                        {{ $totalCancelled }}
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
                Total: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $orders->total() }}</span>
            </p>
        </div>

        <div class="relative">
            <div wire:loading.flex wire:target="search,business_unit,status,date_from,date_to" class="absolute inset-0 z-20 items-center justify-center bg-white/60 dark:bg-black/40 backdrop-blur-sm">
                <div class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm dark:bg-gray-900 dark:text-gray-200 dark:border dark:border-white/10">
                    <i class="fa-thin fa-spinner-third animate-spin"></i>
                    Filtrando…
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-gray-950/60">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:pl-6 dark:text-gray-300">Número</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Fecha</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Unidad</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Compras</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Total</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Estado</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Generada por</th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Acciones</span></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
                        @forelse($orders as $oc)
                            <tr wire:key="oc-{{ $oc->id }}" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                <td class="whitespace-nowrap py-3.5 pl-4 pr-3 text-xs font-mono text-gray-900 sm:pl-6 dark:text-white">
                                    {{ $oc->oc_number }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-gray-700 dark:text-gray-200">
                                    {{ $oc->oc_date->format('Y-m-d') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ \App\Domain\BusinessUnit::tryFrom($oc->business_unit)?->badgeClasses() ?? 'bg-gray-50 text-gray-700' }}">
                                        {{ $oc->business_unit }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-right text-gray-700 dark:text-gray-200">
                                    {{ $oc->total_items }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-right font-semibold text-gray-900 dark:text-white">
                                    $ {{ number_format((float) $oc->total_amount, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs">
                                    @if($oc->isClosed())
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            <i class="fa-thin fa-lock mr-1"></i> Cerrada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-900/30 dark:text-rose-300">
                                            <i class="fa-thin fa-ban mr-1"></i> Anulada
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-xs text-gray-700 dark:text-gray-200">
                                    {{ $oc->creator?->name ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap py-3.5 pl-3 pr-4 text-right text-xs font-medium sm:pr-6">
                                    <div class="inline-flex items-center gap-1">
                                        <button
                                            type="button"
                                            wire:click="showDetail({{ $oc->id }})"
                                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 transition dark:text-gray-300 dark:hover:bg-gray-800"
                                            title="Ver detalle"
                                        >
                                            <i class="fa-thin fa-eye"></i>
                                        </button>
                                        @if($oc->isClosed())
                                            <a
                                                href="{{ route('ordenes-compra.pdf', $oc->id) }}"
                                                target="_blank"
                                                class="inline-flex items-center justify-center rounded-md p-2 text-rose-600 hover:bg-rose-50 transition dark:text-rose-300 dark:hover:bg-rose-900/30"
                                                title="Exportar PDF"
                                            >
                                                <i class="fa-thin fa-file-pdf"></i>
                                            </a>
                                            <button
                                                type="button"
                                                wire:click="cancelConfirmation({{ $oc->id }})"
                                                class="inline-flex items-center justify-center rounded-md p-2 text-amber-600 hover:bg-amber-50 transition dark:text-amber-300 dark:hover:bg-amber-900/30"
                                                title="Anular OC"
                                            >
                                                <i class="fa-thin fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                                    Sin OCs para los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-4 py-3">
            {{ $orders->links() }}
        </div>
    </div>

    {{-- MODAL DETALLE --}}
    @include('livewire.expenses.purchase-order-detail-modal')

    <livewire:confirm-modal />
    <livewire:notification />
</div>
