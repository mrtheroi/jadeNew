{{-- MODAL: Cuadre de caja --}}
@if($showReconciliationModal && $reconciliationSale)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeReconciliation">
        <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-lg dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Cuadre de Caja</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $reconciliationSale->business_unit }} &middot; {{ $reconciliationSale->operation_date->format('Y-m-d') }} &middot; {{ $reconciliationSale->turnoLabel() }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="closeReconciliation"
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition
                           dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                    aria-label="Cerrar"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-4 space-y-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm" x-data>
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/10">
                                <th class="py-2 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Concepto</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sistema</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Corte</th>
                                <th class="pl-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @php
                                $rows = [
                                    ['label' => 'Efectivo', 'field' => 'efectivo_monto'],
                                    ['label' => 'Propina Efectivo', 'field' => 'efectivo_propina'],
                                    ['label' => 'T. Debito', 'field' => 'debito_monto'],
                                    ['label' => 'Propina Debito', 'field' => 'debito_propina'],
                                    ['label' => 'T. Credito', 'field' => 'credito_monto'],
                                    ['label' => 'Propina Credito', 'field' => 'credito_propina'],
                                    ['label' => 'Credito Cliente', 'field' => 'credito_cliente_monto'],
                                    ['label' => 'Propina Cred. Cliente', 'field' => 'credito_cliente_propina'],
                                ];
                                $totalSistema = 0;
                                $totalCorte = 0;
                            @endphp

                            @foreach($rows as $row)
                                @php
                                    $sistema = (float) $reconciliationSale->{$row['field']};
                                    $corteVal = $corte[$row['field']] !== '' ? (float) $corte[$row['field']] : null;
                                    $diff = $corteVal !== null ? $corteVal - $sistema : null;
                                    $totalSistema += $sistema;
                                    if ($corteVal !== null) {
                                        $totalCorte += $corteVal;
                                    }
                                @endphp
                                <tr>
                                    <td class="py-2 pr-3 text-gray-700 dark:text-gray-200">{{ $row['label'] }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-gray-900 dark:text-white">
                                        $ {{ number_format($sistema, 2) }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model.blur="corte.{{ $row['field'] }}"
                                            placeholder="0.00"
                                            class="w-28 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-right text-sm font-mono text-gray-900
                                                   focus:border-indigo-500 focus:ring-indigo-500
                                                   dark:border-white/15 dark:bg-gray-800 dark:text-white"
                                        />
                                    </td>
                                    <td class="pl-3 py-2 text-right font-mono text-sm
                                        @if($diff !== null)
                                            {{ abs($diff) < 0.01 ? 'text-green-600 dark:text-green-400' : ($diff > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}
                                        @else
                                            text-gray-400 dark:text-gray-500
                                        @endif
                                    ">
                                        @if($diff !== null)
                                            {{ $diff >= 0 ? '+' : '' }}$ {{ number_format($diff, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-300 dark:border-white/20">
                            @php
                                $totalDiff = $totalCorte - $totalSistema;
                                $anyCorteValue = collect($corte)->contains(fn ($v) => $v !== '');
                            @endphp
                            <tr class="font-semibold">
                                <td class="py-2 pr-3 text-gray-900 dark:text-white">Total</td>
                                <td class="px-3 py-2 text-right font-mono text-gray-900 dark:text-white">
                                    $ {{ number_format($totalSistema, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right font-mono text-gray-900 dark:text-white">
                                    @if($anyCorteValue)
                                        $ {{ number_format($totalCorte, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="pl-3 py-2 text-right font-mono
                                    @if($anyCorteValue)
                                        {{ abs($totalDiff) < 0.01 ? 'text-green-600 dark:text-green-400' : ($totalDiff > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}
                                    @else
                                        text-gray-400 dark:text-gray-500
                                    @endif
                                ">
                                    @if($anyCorteValue)
                                        {{ $totalDiff >= 0 ? '+' : '' }}$ {{ number_format($totalDiff, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Notas --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Observaciones</label>
                    <textarea
                        wire:model="reconciliationNotes"
                        rows="2"
                        placeholder="Notas sobre el cuadre (opcional)"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500
                               dark:border-white/15 dark:bg-gray-800 dark:text-white"
                    ></textarea>
                </div>

                {{-- Reconciled info --}}
                @if($reconciliationSale->reconciled_at)
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Ultimo cuadre: {{ $reconciliationSale->reconciled_at->format('Y-m-d H:i') }}
                        @if($reconciliationSale->reconciledBy)
                            por {{ $reconciliationSale->reconciledBy->name }}
                        @endif
                    </p>
                @endif
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-white/10">
                <button
                    type="button"
                    wire:click="closeReconciliation"
                    class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition
                           dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="saveReconciliation"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition
                           dark:bg-indigo-500 dark:hover:bg-indigo-400 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="saveReconciliation">Guardar Cuadre</span>
                    <span wire:loading wire:target="saveReconciliation">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif
