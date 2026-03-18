{{-- MODAL: crear / editar insumo --}}
@if($open)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/10">
            {{-- Header modal --}}
            <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4 dark:border-white/10">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $supplyId ? 'Editar insumo' : 'Nuevo insumo' }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Registra la compra del insumo indicando la unidad de negocio, categoría, proveedor y detalles de pago.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="closeModal"
                    class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                >
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>

            {{-- Formulario --}}
            <form wire:submit.prevent="save" class="space-y-5 px-6 py-5">
                {{-- Unidad de negocio / categoría / proveedor (desde Category) --}}
                <div
                    x-data="{
        open: false,
        search: '',
        selectedLabel: '',
        categories: @js(
            $categories->map(fn($c) => [
                'id'    => $c->id,
                'label' => $c->business_unit.' · '.$c->expenseType?->expense_type_name.' · '.$c->expense_name.' · '.$c->provider_name,

            ])
        ),
        init() {
            const currentId = @js($category_id ?? null);
            if (currentId) {
                const found = this.categories.find(c => c.id === currentId);
                if (found) {
                    this.selectedLabel = found.label;
                }
            }
        },
        filtered() {
            if (!this.search) return this.categories;
            const s = this.search.toLowerCase();
            return this.categories.filter(c => c.label.toLowerCase().includes(s));
        },
        select(cat) {
            this.selectedLabel = cat.label;
            this.open = false;
            this.search = '';
            $wire.set('category_id', cat.id);
        }
    }"
                    x-init="init()"
                    class="relative"
                >
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Categoría (unidad / gasto / proveedor)
                    </label>

                    {{-- “Select” visible --}}
                    <button
                        type="button"
                        @click="open = !open"
                        class="mt-1 flex w-full items-center justify-between rounded-md border border-gray-300 bg-white py-2 pl-3 pr-2 text-left text-sm text-gray-900 shadow-sm
               focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500
               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <span x-text="selectedLabel || 'Selecciona una categoría'"></span>
                        <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.186l3.71-3.955a.75.75 0 1 1 1.08 1.04l-4.25 4.53a.75.75 0 0 1-1.08 0l-4.25-4.53a.75.75 0 0 1 .02-1.06z"
                                  clip-rule="evenodd" />
                        </svg>
                    </button>

                    @error('category_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Dropdown con búsqueda --}}
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition
                        class="absolute z-40 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg dark:border-white/15 dark:bg-gray-900"
                    >
                        {{-- Input de búsqueda dentro del dropdown --}}
                        <div class="border-b border-gray-100 p-2 dark:border-white/10">
                            <input
                                type="text"
                                x-model="search"
                                placeholder="Buscar categoría…"
                                class="block w-full rounded-md border border-gray-200 bg-white py-1.5 px-2 text-xs text-gray-900
                       placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500
                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                        </div>

                        {{-- Lista de opciones filtradas --}}
                        <ul class="max-h-56 overflow-auto py-1 text-sm">
                            <template x-for="cat in filtered()" :key="cat.id">
                                <li>
                                    <button
                                        type="button"
                                        @click="select(cat)"
                                        class="flex w-full items-start px-3 py-1.5 text-left text-xs hover:bg-indigo-50 hover:text-indigo-700
                               dark:hover:bg-indigo-950/40 dark:hover:text-indigo-200"
                                    >
                                        <span x-text="cat.label"></span>
                                    </button>
                                </li>
                            </template>

                            <template x-if="filtered().length === 0">
                                <li class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                                    No se encontraron resultados.
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="is_adjustment"
                        type="checkbox"
                        wire:model.live="is_adjustment"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500
               dark:border-white/15 dark:bg-gray-900"
                    >
                    <label for="is_adjustment" class="text-xs text-gray-700 dark:text-gray-200">
                        Es ajuste / devolución (se guardará como negativo)
                    </label>
                </div>


                {{-- Monto del gasto + tipo de pago --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Monto del gasto
                        </label>
                        <div class="mt-1">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.defer="amount"
                                class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500
                                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Ej. 1,250.00"
                            >
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            @if($is_adjustment)
                                Se registrará como <span class="font-semibold text-rose-600 dark:text-rose-400">monto negativo</span>.
                            @else
                                Registro normal (monto positivo).
                            @endif
                        </p>
                        @error('amount')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Tipo de pago
                        </label>
                        <div class="mt-1">
                            <select
                                wire:model.defer="payment_type"
                                class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-sm text-gray-900 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500
                                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">Selecciona tipo</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta_credito">Tarjeta de crédito</option>
                                <option value="tarjeta_debito">Tarjeta de débito</option>
                                <option value="cheque">Cheque</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        @error('payment_type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Fecha de pago / estatus --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Fecha de pago
                        </label>
                        <div class="mt-1">
                            <input
                                type="date"
                                wire:model.defer="payment_date"
                                class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500
                                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                        </div>
                        @error('payment_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Estatus del pago
                        </label>
                        <div class="mt-1">
                            <select
                                wire:model.defer="status"
                                class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-sm text-gray-900 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500
                                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">Selecciona estatus</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Observaciones --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Observaciones
                    </label>
                    <div class="mt-1">
                            <textarea
                                wire:model.defer="notes"
                                rows="3"
                                class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Notas adicionales sobre el pago, acuerdos con el proveedor, etc."
                            ></textarea>
                    </div>
                    @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Footer modal --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-white/10">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm
                                   hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2
                                   focus-visible:outline-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="save"
                    >
                        <svg
                            wire:loading
                            wire:target="save"
                            class="mr-2 h-3.5 w-3.5 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>{{ $supplyId ? 'Guardar cambios' : 'Crear insumo' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
