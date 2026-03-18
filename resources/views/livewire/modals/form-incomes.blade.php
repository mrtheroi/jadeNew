@if($openIncomeModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4 dark:border-white/10">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $incomePeriod ? 'Editar ingreso bancario' : 'Capturar ingreso bancario' }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Registra el ingreso del banco y asígnalo a una unidad y periodo.
                    </p>
                </div>

                <button type="button" wire:click="closeIncome"
                        class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="saveIncome" class="space-y-4 px-6 py-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Unidad de negocio (SELECT) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                            Unidad de negocio
                        </label>

                        <select
                            wire:model.defer="business_unit"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-1.5 px-2 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        >
                            <option value="">Selecciona una unidad</option>
                            <option value="Jade">Jade</option>
                            <option value="Fuego Ambar">Fuego Ambar</option>
                            <option value="KIN">KIN</option>
                        </select>

                        @error('business_unit')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Periodo (MONTH) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                            Periodo (YYYY-MM)
                        </label>

                        <input
                            type="month"
                            wire:model.defer="period_key"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-1.5 px-2 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        >

                        @error('period_key')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Ingreso --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                        Ingreso bancario (monto)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.defer="income_amount"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-1.5 px-2 text-xs text-gray-900 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        placeholder="Ej. 1622271.29"
                    >
                    @error('income_amount')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nota --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                        Nota (opcional)
                    </label>
                    <textarea
                        rows="3"
                        wire:model.defer="income_notes"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-1.5 px-2 text-xs text-gray-900 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        placeholder="Ej. Depósitos banco + terminal + ajustes..."
                    ></textarea>
                    @error('income_notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-white/10">
                    <button
                        type="button"
                        wire:click="closeIncome"
                        class="rounded-md border border-gray-300 bg-white py-1.5 px-3 text-xs font-semibold text-gray-700 shadow-sm
                               hover:bg-gray-50
                               dark:border-white/20 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md bg-emerald-600 py-1.5 px-3 text-xs font-semibold text-white shadow-sm
                               hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2
                               focus-visible:outline-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="saveIncome"
                    >
                        <svg wire:loading wire:target="saveIncome"
                             class="mr-2 h-4 w-4 animate-spin text-white"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Guardar ingreso
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
