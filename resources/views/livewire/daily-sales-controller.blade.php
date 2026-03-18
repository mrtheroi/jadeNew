<div class="space-y-4">

    {{-- HEADER --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Ventas Diarias</h2>
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        {{ $period_key }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Rango: <span class="font-medium">{{ $from_date }}</span> → <span class="font-medium">{{ $to_date }}</span>
                </p>
            </div>

            {{-- ACTIONS --}}
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    wire:click="create"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition
                           dark:bg-indigo-500 dark:hover:bg-indigo-400"
                >
                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    Subir PDF
                </button>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="mt-4 grid gap-3 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Unidad</label>
                <select
                    wire:model.live="filterBusinessUnit"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-indigo-500 focus:ring-indigo-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="">Todas</option>
                    <option value="Jade">Jade</option>
                    <option value="Fuego Ambar">Fuego Ambar</option>
                    <option value="KIN">KIN</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Periodo</label>
                <input
                    type="month"
                    wire:model.live="period_key"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                           focus:border-indigo-500 focus:ring-indigo-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>

            <div class="lg:col-span-3 flex items-end justify-start gap-2">
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center justify-center rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
                >
                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    {{-- CARDS --}}
    <div class="grid gap-3 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Subtotal</p>
            </div>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                $ {{ number_format((float) ($totals->total_subtotal ?? 0), 2) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">IVA</p>
            </div>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                $ {{ number_format((float) ($totals->total_iva ?? 0), 2) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Ventas</p>
            </div>
            <p class="mt-2 text-lg font-semibold text-emerald-600 dark:text-emerald-400">
                $ {{ number_format((float) ($totals->total_total ?? 0), 2) }}
            </p>
        </div>
    </div>

    {{-- DESGLOSE CARDS --}}
    <div class="grid gap-3 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Alimentos</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                $ {{ number_format((float) ($totals->total_alimentos ?? 0), 2) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Bebidas</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                $ {{ number_format((float) ($totals->total_bebidas ?? 0), 2) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Otros</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                $ {{ number_format((float) ($totals->total_otros ?? 0), 2) }}
            </p>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between px-4 py-3">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Registros</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Total: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $sales->total() }}</span>
            </p>
        </div>

        <div class="relative">
            <div wire:loading.flex wire:target="filterBusinessUnit,period_key" class="absolute inset-0 z-20 items-center justify-center bg-white/60 dark:bg-black/40 backdrop-blur-sm">
                <div class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm
                            dark:bg-gray-900 dark:text-gray-200 dark:border dark:border-white/10">
                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Filtrando…
                </div>
            </div>

            <div class="overflow-x-auto max-h-[70vh]">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-950/60 backdrop-blur">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:pl-6 dark:text-gray-300">Fecha</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Unidad</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Turno</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Status</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Subido por</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Alimentos</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Bebidas</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Otros</th>
                        <th class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Total</th>
                        <th class="py-3.5 pr-4 pl-3 text-right sm:pr-6">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($sales as $sale)
                        @php
                            $buClass = match($sale->business_unit) {
                                'Jade' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300',
                                'Fuego Ambar' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300',
                                'KIN' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-900/30 dark:text-indigo-300',
                                default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-900/30 dark:text-gray-200',
                            };

                            $statusClass = match($sale->status) {
                                'completed' => 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-300',
                                'processing' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'failed' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-300',
                                default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-900/30 dark:text-gray-200',
                            };

                            $statusLabel = match($sale->status) {
                                'completed' => 'Completado',
                                'processing' => 'Procesando',
                                'failed' => 'Fallido',
                                default => $sale->status,
                            };
                        @endphp

                        <tr wire:key="sale-{{ $sale->id }}" class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                            <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm text-gray-900 sm:pl-6 dark:text-white">
                                {{ $sale->operation_date->format('Y-m-d') }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $buClass }}">
                                    {{ $sale->business_unit }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-900 dark:text-white">
                                {{ $sale->turnoLabel() }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-900 dark:text-white">
                                {{ $sale->user?->name ?? '-' }}
                            </td>
                            @if($sale->isCompleted())
                                <td class="whitespace-nowrap px-3 py-3 text-right text-sm text-gray-900 dark:text-white">
                                    $ {{ number_format((float) $sale->alimentos, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-right text-sm text-gray-900 dark:text-white">
                                    $ {{ number_format((float) $sale->bebidas, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-right text-sm text-gray-900 dark:text-white">
                                    $ {{ number_format((float) $sale->otros, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-right text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    $ {{ number_format((float) $sale->total, 2) }}
                                </td>
                            @else
                                <td class="whitespace-nowrap px-3 py-3 text-right text-sm text-gray-400 dark:text-gray-500" colspan="4">
                                    @if($sale->isProcessing())
                                        <span class="italic">En proceso...</span>
                                    @else
                                        <span class="italic">{{ $sale->error_message ?? 'Error' }}</span>
                                    @endif
                                </td>
                            @endif
                            <td class="whitespace-nowrap py-3 pr-4 pl-3 text-right text-sm sm:pr-6">
                                <div class="flex items-center justify-end gap-1">
                                    @if($sale->isCompleted())
                                        <button
                                            type="button"
                                            wire:click="showDetail({{ $sale->id }})"
                                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900
                                                   dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white transition"
                                            aria-label="Ver detalle"
                                        >
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </button>
                                    @endif

                                    @if($sale->isFailed())
                                        <button
                                            type="button"
                                            wire:click="retry({{ $sale->id }})"
                                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-amber-600 hover:bg-amber-50
                                                   dark:text-amber-300 dark:hover:bg-amber-900/30 transition"
                                            aria-label="Reintentar"
                                        >
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                                        </button>
                                    @endif

                                    @if(! $sale->isCompleted())
                                        <button
                                            type="button"
                                            wire:click="deleteConfirmation({{ $sale->id }})"
                                            wire:loading.attr="disabled"
                                            class="group relative inline-flex items-center justify-center rounded-md p-2 text-rose-600 hover:bg-rose-50
                                                   dark:text-rose-300 dark:hover:bg-rose-900/30 transition disabled:opacity-50"
                                            aria-label="Eliminar"
                                        >
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-10 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <svg class="h-8 w-8 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" /></svg>
                                    <p class="text-sm font-semibold">No hay registros de ventas</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Sube un archivo PDF para cargar las ventas del dia.
                                    </p>
                                    <button
                                        type="button"
                                        wire:click="create"
                                        class="mt-2 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition
                                               dark:bg-indigo-500 dark:hover:bg-indigo-400"
                                    >
                                        <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                        Subir PDF
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-4 py-3 sm:px-6">
            {{ $sales->links() }}
        </div>

        @livewire('confirm-modal')
    </div>

    {{-- MODALS --}}
    @include('livewire.modals.form-daily-sales')
    @include('livewire.modals.detail-daily-sales')

</div>
