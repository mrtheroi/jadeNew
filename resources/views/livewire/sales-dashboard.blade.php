<div class="space-y-6">

    {{-- HEADER --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Dashboard</h2>
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        {{ $this->business_unit ?: 'Todas' }} · {{ $this->period_key }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Resumen consolidado: ventas, gastos y utilidad.
                </p>
            </div>

        </div>

        {{-- FILTERS + ACTIONS --}}
        <div class="mt-4 grid gap-3 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Unidad</label>
                <select wire:model.live="business_unit"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Todas</option>
                    @foreach(\App\Domain\BusinessUnit::cases() as $bu)
                        <option value="{{ $bu->value }}">{{ $bu->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Periodo</label>
                <input type="month" wire:model.live="period_key"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100" />
            </div>
            <div class="lg:col-span-6 flex items-end justify-end gap-3">
                {{-- Grupo: Ventas --}}
                <div class="flex flex-col items-center gap-1">
                    <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Ventas</span>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('dashboard.ventas.export.excel', ['period_key' => $this->period_key, 'business_unit' => $this->business_unit]) }}"
                           class="group relative inline-flex items-center justify-center rounded-md p-2 text-emerald-600 hover:bg-emerald-50 transition
                                  dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                           aria-label="Ventas Excel">
                            <svg class="size-[15px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M12 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M21.375 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M12 14.625v-1.5m0 1.5c0 .621.504 1.125 1.125 1.125M12 14.625c0 .621-.504 1.125-1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 0v1.5c0 .621-.504 1.125-1.125 1.125" />
                            </svg>
                            <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0 shadow-sm transition group-hover:opacity-100 dark:bg-black">Excel</span>
                        </a>
                        <a href="{{ route('dashboard.ventas.export.pdf', ['period_key' => $this->period_key, 'business_unit' => $this->business_unit]) }}"
                           class="group relative inline-flex items-center justify-center rounded-md p-2 text-rose-600 hover:bg-rose-50 transition
                                  dark:text-rose-300 dark:hover:bg-rose-900/30"
                           aria-label="Ventas PDF">
                            <svg class="size-[15px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0 shadow-sm transition group-hover:opacity-100 dark:bg-black">PDF</span>
                        </a>
                    </div>
                </div>

                {{-- Separador --}}
                <div class="h-8 w-px bg-gray-200 dark:bg-white/10"></div>

                {{-- Grupo: Edo. Resultados --}}
                <div class="flex flex-col items-center gap-1">
                    <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Edo. Resultados</span>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('dashboard.estado-resultados.export.excel', ['period_key' => $this->period_key, 'business_unit' => $this->business_unit]) }}"
                           class="group relative inline-flex items-center justify-center rounded-md p-2 text-emerald-600 hover:bg-emerald-50 transition
                                  dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                           aria-label="Edo. Resultados Excel">
                            <svg class="size-[15px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M12 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M21.375 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M12 14.625v-1.5m0 1.5c0 .621.504 1.125 1.125 1.125M12 14.625c0 .621-.504 1.125-1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 0v1.5c0 .621-.504 1.125-1.125 1.125" />
                            </svg>
                            <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0 shadow-sm transition group-hover:opacity-100 dark:bg-black">Excel</span>
                        </a>
                        <a href="{{ route('dashboard.estado-resultados.export.pdf', ['period_key' => $this->period_key, 'business_unit' => $this->business_unit]) }}"
                           class="group relative inline-flex items-center justify-center rounded-md p-2 text-rose-600 hover:bg-rose-50 transition
                                  dark:text-rose-300 dark:hover:bg-rose-900/30"
                           aria-label="Edo. Resultados PDF">
                            <svg class="size-[15px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0 shadow-sm transition group-hover:opacity-100 dark:bg-black">PDF</span>
                        </a>
                    </div>
                </div>

                {{-- Separador --}}
                <div class="h-8 w-px bg-gray-200 dark:bg-white/10"></div>

                {{-- Limpiar --}}
                <button type="button" wire:click="clearFilters"
                    class="inline-flex items-center justify-center rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5">
                    <svg class="mr-2 size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3ZM16.5 16.5l5 5m0-5-5 5" /></svg>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    {{-- FILA 1: KPIs --}}
    <div class="grid gap-3 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Ventas</p>
            <p class="mt-2 text-xl font-semibold text-emerald-600 dark:text-emerald-400">$ {{ number_format($this->totalSales, 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Subtotal: ${{ number_format($this->totalSubtotal, 2) }} + IVA: ${{ number_format($this->totalIva, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Gastos</p>
            <p class="mt-2 text-xl font-semibold text-rose-600 dark:text-rose-400">$ {{ number_format($this->totalExpenses, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Utilidad</p>
            <p class="mt-2 text-xl font-semibold {{ $this->profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">$ {{ number_format($this->profit, 2) }}</p>
            @if($this->totalSales > 0)
                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Margen: {{ number_format(($this->profit / $this->totalSales) * 100, 1) }}%</p>
            @endif
        </div>
    </div>

    {{-- FILA 2: Desglose categorias --}}
    <div class="grid gap-3 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Alimentos</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->totalAlimentos, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Bebidas</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->totalBebidas, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Otros</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->totalOtros, 2) }}</p>
        </div>
    </div>

    {{-- FILA 3: Metodos de pago --}}
    <div class="grid gap-3 md:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Efectivo</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->efectivoMonto, 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Propina: $ {{ number_format($this->efectivoPropina, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">T. Debito</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->debitoMonto, 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Propina: $ {{ number_format($this->debitoPropina, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">T. Credito</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->creditoMonto, 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Propina: $ {{ number_format($this->creditoPropina, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Credito Cliente</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->creditoClienteMonto, 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Propina: $ {{ number_format($this->creditoClientePropina, 2) }}</p>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-2 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Propinas</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->totalPropinas, 2) }}</p>
        </div>
    </div>

    {{-- FILA 4: Operativas + Turnos --}}
    <div class="grid gap-3 lg:grid-cols-2">
        {{-- Ticket promedio --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Metricas operativas</h3>
            <div class="mt-3 grid grid-cols-3 gap-3 text-center">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Ticket promedio</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">$ {{ number_format($this->ticketPromedio, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Personas</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($this->totalPersonas) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Cuentas</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($this->totalCuentas) }}</p>
                </div>
            </div>
        </div>

        {{-- Comparativo turnos --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Comparativo por turno</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="pb-2 text-left font-semibold text-gray-500 dark:text-gray-400">Turno</th>
                            <th class="pb-2 text-right font-semibold text-gray-500 dark:text-gray-400">Ventas</th>
                            <th class="pb-2 text-right font-semibold text-gray-500 dark:text-gray-400">Personas</th>
                            <th class="pb-2 text-right font-semibold text-gray-500 dark:text-gray-400">Ticket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <tr>
                            <td class="py-2 font-medium text-gray-900 dark:text-white">Matutino</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">$ {{ number_format($this->turno1Ventas, 2) }}</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($this->turno1Personas) }}</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">$ {{ number_format($this->turno1Ticket, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium text-gray-900 dark:text-white">Vespertino</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">$ {{ number_format($this->turno2Ventas, 2) }}</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($this->turno2Personas) }}</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">$ {{ number_format($this->turno2Ticket, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FILA 5: CHARTS --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Ventas por unidad de negocio</h2>
            <div class="mt-4" wire:ignore>
                <canvas id="chartByUnit" class="w-full h-64"></canvas>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Distribucion por metodo de pago</h2>
            <div class="mt-4" wire:ignore>
                <canvas id="paymentChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>

    {{-- FILA 6: ESTADO DE RESULTADOS --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Estado de Resultados</h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Desglose de gastos por tipo agrupado vs ingreso total.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-800/50">
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Concepto</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-700 dark:text-gray-200">Monto</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-700 dark:text-gray-200">% Ingreso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                        <td class="px-4 py-2 font-bold text-emerald-800 dark:text-emerald-300">INGRESO</td>
                        <td class="px-4 py-2 text-right font-bold text-emerald-800 dark:text-emerald-300">$ {{ number_format($this->totalSales, 2) }}</td>
                        <td class="px-4 py-2 text-right font-bold text-emerald-800 dark:text-emerald-300">100%</td>
                    </tr>
                    <tr class="text-[10px] text-gray-500 dark:text-gray-400">
                        <td class="px-4 py-1 pl-8">Alimentos: ${{ number_format($this->totalAlimentos, 2) }} | Bebidas: ${{ number_format($this->totalBebidas, 2) }} | Otros: ${{ number_format($this->totalOtros, 2) }}</td>
                        <td class="px-4 py-1 text-right">Sub: ${{ number_format($this->totalSubtotal, 2) }}</td>
                        <td class="px-4 py-1 text-right">IVA: ${{ number_format($this->totalIva, 2) }}</td>
                    </tr>
                    @foreach($expenseGroups as $group)
                        <tr wire:key="group-{{ $loop->index }}" class="bg-gray-50 dark:bg-gray-800/30">
                            <td class="px-4 py-2 font-bold text-gray-800 dark:text-gray-100" colspan="3">{{ strtoupper($group['expense_type']) }}</td>
                        </tr>
                        @foreach($group['categories'] as $cat)
                            <tr wire:key="group-{{ $loop->parent->index }}-cat-{{ $loop->index }}" class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="py-1.5 pl-8 pr-4 text-gray-700 dark:text-gray-300">{{ $cat['name'] }}</td>
                                <td class="px-4 py-1.5 text-right text-gray-700 dark:text-gray-300">$ {{ number_format($cat['amount'], 2) }}</td>
                                <td class="px-4 py-1.5 text-right text-gray-500 dark:text-gray-400">{{ $cat['percent'] }}%</td>
                            </tr>
                        @endforeach
                        <tr wire:key="group-{{ $loop->index }}-subtotal" class="border-t border-gray-200 bg-gray-100 dark:border-white/10 dark:bg-gray-800/50">
                            <td class="px-4 py-1.5 pl-8 font-semibold text-gray-800 dark:text-gray-100">Total {{ $group['expense_type'] }}</td>
                            <td class="px-4 py-1.5 text-right font-semibold text-gray-800 dark:text-gray-100">$ {{ number_format($group['total'], 2) }}</td>
                            <td class="px-4 py-1.5 text-right font-semibold text-gray-500 dark:text-gray-400">{{ $group['percent'] }}%</td>
                        </tr>
                    @endforeach
                    <tr class="border-t-2 border-rose-300 bg-rose-50 dark:border-rose-700 dark:bg-rose-900/20">
                        <td class="px-4 py-2 font-bold text-rose-800 dark:text-rose-300">TOTAL GASTOS</td>
                        <td class="px-4 py-2 text-right font-bold text-rose-800 dark:text-rose-300">$ {{ number_format($this->totalExpenses, 2) }}</td>
                        <td class="px-4 py-2 text-right font-bold text-rose-800 dark:text-rose-300">{{ $this->totalSales > 0 ? number_format(($this->totalExpenses / $this->totalSales) * 100, 2) : '0' }}%</td>
                    </tr>
                    <tr class="border-t-2 border-emerald-300 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-900/20">
                        <td class="px-4 py-2 font-bold {{ $this->profit >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">UTILIDAD</td>
                        <td class="px-4 py-2 text-right font-bold {{ $this->profit >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">$ {{ number_format($this->profit, 2) }}</td>
                        <td class="px-4 py-2 text-right font-bold {{ $this->profit >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">{{ $this->totalSales > 0 ? number_format(($this->profit / $this->totalSales) * 100, 2) : '0' }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                let chartByUnit = null;
                let paymentChart = null;

                const paymentColors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'];

                function normalizePayload(payload) {
                    if (payload && payload.data) payload = payload.data;
                    return {
                        labelsUnits:   Array.isArray(payload?.labelsUnits)   ? payload.labelsUnits   : [],
                        dataUnits:     Array.isArray(payload?.dataUnits)     ? payload.dataUnits     : [],
                        paymentLabels: Array.isArray(payload?.paymentLabels) ? payload.paymentLabels : [],
                        paymentData:   Array.isArray(payload?.paymentData)   ? payload.paymentData   : [],
                    };
                }

                function destroyChartsIfAny() {
                    if (chartByUnit) { chartByUnit.destroy(); chartByUnit = null; }
                    if (paymentChart) { paymentChart.destroy(); paymentChart = null; }
                }

                function ensureCharts(rawPayload) {
                    const unitCanvas = document.getElementById('chartByUnit');
                    const payCanvas = document.getElementById('paymentChart');
                    if (!unitCanvas || !payCanvas) return;

                    destroyChartsIfAny();
                    const { labelsUnits, dataUnits, paymentLabels, paymentData } = normalizePayload(rawPayload);

                    chartByUnit = new Chart(unitCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: labelsUnits,
                            datasets: [{ label: 'Total vendido', data: dataUnits, borderWidth: 1 }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });

                    paymentChart = new Chart(payCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: paymentLabels,
                            datasets: [{ data: paymentData, backgroundColor: paymentColors, borderWidth: 1 }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'bottom' } }
                        }
                    });
                }

                function updateCharts(rawPayload) {
                    const { labelsUnits, dataUnits, paymentLabels, paymentData } = normalizePayload(rawPayload);

                    if (!chartByUnit || !paymentChart) {
                        ensureCharts(rawPayload);
                        return;
                    }

                    chartByUnit.data.labels = labelsUnits;
                    chartByUnit.data.datasets[0].data = dataUnits;
                    chartByUnit.update();

                    paymentChart.data.labels = paymentLabels;
                    paymentChart.data.datasets[0].data = paymentData;
                    paymentChart.update();
                }

                window.addEventListener('livewire:navigated', () => {
                    const initialPayload = {
                        labelsUnits:   @json($labelsUnits ?? []),
                        dataUnits:     @json($dataUnits ?? []),
                        paymentLabels: @json($paymentLabels ?? []),
                        paymentData:   @json($paymentData ?? []),
                    };
                    setTimeout(() => ensureCharts(initialPayload), 0);
                });

                document.addEventListener('livewire:init', () => {
                    Livewire.on('chart-data-updated', (event) => {
                        updateCharts(event.data);
                    });
                });

                document.addEventListener('DOMContentLoaded', () => {
                    const initialPayload = {
                        labelsUnits:   @json($labelsUnits ?? []),
                        dataUnits:     @json($dataUnits ?? []),
                        paymentLabels: @json($paymentLabels ?? []),
                        paymentData:   @json($paymentData ?? []),
                    };
                    ensureCharts(initialPayload);
                });
            })();
        </script>
    @endpush

</div>
