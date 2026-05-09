{{-- MODAL: detalle de OC con desglose por proveedor --}}
@if($showDetailModal && $detailOc)
    <x-modal wire:model="showDetailModal" maxWidth="2xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $detailOc->oc_number }}
                </h3>
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    Generada {{ $detailOc->closed_at?->format('Y-m-d H:i') }} por {{ $detailOc->creator?->name ?? '—' }}
                </p>
            </div>
            <button type="button" wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
            {{-- Header de la OC --}}
            <div class="grid grid-cols-3 gap-3 text-xs">
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fecha</div>
                    <div class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $detailOc->oc_date->format('Y-m-d') }}</div>
                </div>
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide">Unidad</div>
                    <div class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $detailOc->business_unit }}</div>
                </div>
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/40">
                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</div>
                    <div class="mt-1 font-semibold {{ $detailOc->isClosed() ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                        {{ $detailOc->isClosed() ? 'Cerrada' : 'Anulada' }}
                    </div>
                </div>
            </div>

            {{-- Desglose agrupado por proveedor --}}
            @php
                $byProvider = $detailOc->supplies->groupBy(fn ($s) => $s->category?->provider_name ?? 'Sin proveedor');
            @endphp

            <div>
                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Compras agrupadas por proveedor
                </h4>

                <div class="space-y-3">
                    @foreach($byProvider as $providerName => $items)
                        @php
                            $providerSubtotal = (float) $items->sum('amount');
                        @endphp
                        <div wire:key="provider-{{ $loop->index }}" class="rounded-lg border border-gray-200 dark:border-white/10">
                            <div class="flex items-center justify-between bg-gray-50 px-3 py-2 dark:bg-gray-800/40">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                                    <i class="fa-thin fa-building mr-1"></i>
                                    {{ $providerName }}
                                </span>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                    $ {{ number_format($providerSubtotal, 2) }}
                                </span>
                            </div>

                            <div class="divide-y divide-gray-200 dark:divide-white/10">
                                @foreach($items as $supply)
                                    <div wire:key="item-{{ $supply->id }}" class="flex items-center justify-between px-3 py-2 text-xs">
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ $supply->category?->expenseType?->expense_type_name ?? '—' }} — {{ $supply->category?->expense_name ?? '—' }}
                                            </div>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ $supply->payment_type ?? '—' }} · {{ ucfirst($supply->status ?? '—') }}
                                            </div>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold text-gray-600 dark:text-gray-300">Solicitó:</span>
                                                {{ $supply->requester?->full_name ?? '—' }}
                                                <span class="mx-1 text-gray-400">·</span>
                                                <span class="font-semibold text-gray-600 dark:text-gray-300">Aprobó:</span>
                                                {{ $supply->approver?->full_name ?? '—' }}
                                            </div>
                                        </div>
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            $ {{ number_format((float) $supply->amount, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Total --}}
            <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4 dark:border-emerald-500/20 dark:bg-emerald-900/10">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">TOTAL OC</span>
                    <span class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                        $ {{ number_format((float) $detailOc->total_amount, 2) }}
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    {{ $detailOc->total_items }} {{ $detailOc->total_items === 1 ? 'compra' : 'compras' }} consolidadas
                </p>
            </div>

            @if($detailOc->notes)
                <div class="rounded-lg bg-gray-50 p-3 text-xs dark:bg-gray-800/40">
                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Notas</div>
                    <div class="text-gray-700 dark:text-gray-200">{{ $detailOc->notes }}</div>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between gap-2 border-t border-gray-200 px-4 py-3 dark:border-white/10">
            <div class="flex items-center gap-2">
                @if($detailOc->isClosed())
                    <a
                        href="{{ route('ordenes-compra.pdf', $detailOc->id) }}"
                        target="_blank"
                        class="inline-flex items-center rounded-md bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-500 transition
                               dark:bg-rose-500 dark:hover:bg-rose-400"
                    >
                        <i class="fa-thin fa-file-pdf mr-2"></i>
                        Exportar PDF
                    </a>
                    <button
                        type="button"
                        wire:click="cancelConfirmation({{ $detailOc->id }})"
                        class="inline-flex items-center rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition
                               dark:border-amber-500/30 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50"
                    >
                        <i class="fa-thin fa-ban mr-2"></i>
                        Anular OC
                    </button>
                @endif
            </div>

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
