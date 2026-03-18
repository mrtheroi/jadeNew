{{-- MODAL: detalle de venta diaria --}}
@if($showDetailModal && $detailSale)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeDetail">
        <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-lg dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detalle de venta</h3>
                <button
                    type="button"
                    wire:click="closeDetail"
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition
                           dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                    aria-label="Cerrar"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-4 space-y-4 text-sm text-gray-800 dark:text-gray-100">

                {{-- Info general --}}
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Informacion general</h4>
                    <div class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Unidad</div>
                            <div class="font-semibold">{{ $detailSale->business_unit }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Fecha</div>
                            <div class="font-semibold">{{ $detailSale->operation_date->format('Y-m-d') }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Turno</div>
                            <div class="font-semibold">{{ $detailSale->turnoLabel() }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Periodo</div>
                            <div class="font-semibold">
                                @if($detailSale->period_start && $detailSale->period_end)
                                    {{ $detailSale->period_start->format('H:i') }} - {{ $detailSale->period_end->format('H:i') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ventas --}}
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ventas</h4>
                    <div class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Alimentos</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->alimentos, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Bebidas</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->bebidas, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Otros</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->otros, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Subtotal</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->subtotal, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">IVA</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->iva, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Total</div>
                            <div class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                                $ {{ number_format((float) $detailSale->total, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Metodos de pago --}}
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Metodos de pago</h4>
                    <div class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Efectivo</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->efectivo_monto, 2) }}</div>
                            <div class="text-[10px] text-gray-400">Propina: $ {{ number_format((float) $detailSale->efectivo_propina, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">T. Debito</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->debito_monto, 2) }}</div>
                            <div class="text-[10px] text-gray-400">Propina: $ {{ number_format((float) $detailSale->debito_propina, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">T. Credito</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->credito_monto, 2) }}</div>
                            <div class="text-[10px] text-gray-400">Propina: $ {{ number_format((float) $detailSale->credito_propina, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Credito Cliente</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->credito_cliente_monto, 2) }}</div>
                            <div class="text-[10px] text-gray-400">Propina: $ {{ number_format((float) $detailSale->credito_cliente_propina, 2) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Datos operativos --}}
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Datos operativos</h4>
                    <div class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Personas</div>
                            <div class="font-semibold">{{ number_format($detailSale->numero_personas) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Cuentas</div>
                            <div class="font-semibold">{{ number_format($detailSale->numero_cuentas) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Ticket promedio</div>
                            <div class="font-semibold">$ {{ number_format((float) $detailSale->promedio_por_persona, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                            <div class="text-gray-500 dark:text-gray-400">Productos</div>
                            <div class="font-semibold">{{ number_format($detailSale->cantidad_productos) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Subido por --}}
                @if($detailSale->user)
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40 text-xs">
                        <div class="text-gray-500 dark:text-gray-400">Subido por</div>
                        <div class="font-semibold">{{ $detailSale->user->name }} · {{ $detailSale->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                @endif
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
