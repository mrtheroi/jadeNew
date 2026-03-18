{{-- MODAL: detalle de insumo --}}
@if($showDetailModal && $detailSupply)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/10">
            {{-- Header --}}
            <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4 dark:border-white/10">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Detalle del insumo
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $detailSupply->category?->business_unit }} ·
                        {{ $detailSupply->category?->expense_name }} ·
                        {{ $detailSupply->category?->provider_name }}
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="closeDetail"
                    class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                >
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>

            @php
                $pDate = $detailSupply->payment_date;
                $now = now();
                $isSameMonthDetail = $pDate ? $pDate->isSameMonth($now) : false;
                $isOverdueDetail = $pDate
                    ? $pDate->lt($now->copy()->startOfMonth()) && $detailSupply->status !== 'pagado'
                    : false;
            @endphp

            <div class="grid gap-6 px-6 py-5 sm:grid-cols-2">
                {{-- Columna 1: info general --}}
                <div class="space-y-3 text-sm">
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Información general
                    </h4>

                    <dl class="space-y-2">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Unidad de negocio</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">
                                {{ $detailSupply->category?->business_unit ?? '—' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Categoría</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">
                                {{ $detailSupply->category?->expense_name ?? '—' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Proveedor</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">
                                {{ $detailSupply->category?->provider_name ?? '—' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Monto gasto</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">
                                $ {{ number_format($detailSupply->amount, 2) }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Tipo de pago</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">
                                {{ ucfirst($detailSupply->payment_type ?? '—') }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Columna 2: fechas / estatus --}}
                <div class="space-y-3 text-sm">
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Fechas y estado
                    </h4>

                    <dl class="space-y-2">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Fecha de pago</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">
                                {{ $pDate?->format('d/m/Y') ?? '—' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Mes de pago</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">
                                {{ $pDate?->translatedFormat('F Y') ?? ($detailSupply->payment_month ?? '—') }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Estatus</dt>
                            <dd class="text-right">
                                <span class="inline-flex items-center rounded-md
                                             @switch($detailSupply->status)
                                                @case('pagado')
                                                    bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-500/50
                                                    @break
                                                @case('pendiente')
                                                    bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-500/50
                                                    @break
                                                @default
                                                    bg-gray-50 text-gray-700 ring-gray-400/20 dark:bg-gray-900/40 dark:text-gray-200 dark:ring-gray-500/40
                                             @endswitch
                                             px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                    {{ ucfirst($detailSupply->status ?? '—') }}
                                </span>
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Situación</dt>
                            <dd class="text-right">
                                @if($isOverdueDetail)
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-500/50">
                                        Vencido
                                    </span>
                                @elseif($isSameMonthDetail && $detailSupply->status === 'pagado')
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-500/50">
                                        Pagado (mes corriente)
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-400/20 dark:bg-gray-900/40 dark:text-gray-200 dark:ring-gray-500/40">
                                        En curso
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="border-t border-gray-100 px-6 py-4 text-sm dark:border-white/10">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5">
                    Observaciones
                </h4>
                <p class="text-sm text-gray-700 dark:text-gray-200">
                    {{ $detailSupply->notes ?: 'Sin observaciones registradas.' }}
                </p>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-3 dark:border-white/10">
                <button
                    type="button"
                    wire:click="closeDetail"
                    class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
@endif
