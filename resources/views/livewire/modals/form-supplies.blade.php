{{-- MODAL: crear / editar insumo --}}
<x-modal wire:model="open" maxWidth="2xl">
    {{-- Header modal --}}
    <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4 dark:border-white/10">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $form->supplyId ? 'Editar insumo' : 'Nuevo insumo' }}
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Registra la compra del insumo indicando la unidad de negocio, categoría, proveedor y detalles de pago.
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <form wire:submit.prevent="save" class="space-y-5 px-6 py-5">
        {{-- Unidad de negocio / categoría / proveedor (desde Category) --}}
        <div
            x-data="{
                open: false,
                search: '',
                selectedId: @js($form->category_id ?? null),
                selectedLabel: '',
                categories: @js(
                    $categories->map(fn($c) => [
                        'id'    => $c->id,
                        'label' => $c->business_unit.' · '.$c->expenseType?->expense_type_name.' · '.$c->expense_name.' · '.$c->provider_name,
                    ])
                ),
                init() {
                    if (this.selectedId) {
                        const found = this.categories.find(c => c.id === this.selectedId);
                        if (found) this.selectedLabel = found.label;
                    }
                },
                filtered() {
                    if (!this.search) return this.categories;
                    const s = this.search.toLowerCase();
                    return this.categories.filter(c => c.label.toLowerCase().includes(s));
                },
                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.search = '';
                        this.$nextTick(() => this.$refs.searchInput.focus());
                    }
                },
                select(cat) {
                    this.selectedId = cat.id;
                    this.selectedLabel = cat.label;
                    this.open = false;
                    this.search = '';
                    $wire.set('form.category_id', cat.id);
                },
                close() {
                    this.open = false;
                    this.search = '';
                }
            }"
            x-init="init()"
            @keydown.escape.stop="if (open) close()"
            class="relative"
        >
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                Categoría (unidad / gasto / proveedor)
            </label>

            {{-- Trigger button --}}
            <button
                type="button"
                @click="toggle()"
                class="mt-1 flex w-full items-center justify-between rounded-md border border-gray-300 bg-white py-2 pl-3 pr-2 text-left text-sm text-gray-900 shadow-sm
                       focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500
                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
            >
                <span
                    x-text="selectedLabel || 'Selecciona una categoría'"
                    :class="selectedLabel ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500'"
                ></span>
                <svg class="size-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.186l3.71-3.955a.75.75 0 1 1 1.08 1.04l-4.25 4.53a.75.75 0 0 1-1.08 0l-4.25-4.53a.75.75 0 0 1 .02-1.06z"
                          clip-rule="evenodd" />
                </svg>
            </button>

            @error('form.category_id')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

            {{-- Dropdown --}}
            <div
                x-show="open"
                @click.outside="close()"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute z-40 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg dark:border-white/15 dark:bg-gray-900"
            >
                {{-- Search input --}}
                <div class="border-b border-gray-100 p-2 dark:border-white/10">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-2 top-1/2 size-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            type="text"
                            x-ref="searchInput"
                            x-model="search"
                            placeholder="Buscar categoría…"
                            class="block w-full rounded-md border border-gray-200 bg-white py-1.5 pl-8 pr-2 text-xs text-gray-900
                                   placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        >
                    </div>
                </div>

                {{-- Options list --}}
                <ul class="max-h-56 overflow-auto py-1 text-sm">
                    <template x-for="cat in filtered()" :key="cat.id">
                        <li>
                            <button
                                type="button"
                                @click="select(cat)"
                                class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-xs transition
                                       hover:bg-emerald-50 hover:text-emerald-700
                                       dark:hover:bg-emerald-950/40 dark:hover:text-emerald-200"
                                :class="cat.id === selectedId ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' : 'text-gray-900 dark:text-gray-100'"
                            >
                                <svg x-show="cat.id === selectedId" class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span x-show="cat.id !== selectedId" class="size-4 shrink-0"></span>
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
                wire:model.live="form.is_adjustment"
                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500
                       dark:border-white/15 dark:bg-gray-900"
            >
            <label for="is_adjustment" class="text-xs text-gray-700 dark:text-gray-200">
                Es ajuste / devolución (se guardará como negativo)
            </label>
        </div>


        {{-- Monto del gasto + tipo de pago --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-form-field label="Monto del gasto" name="form.amount">
                    <input
                        id="form.amount"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.defer="form.amount"
                        class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        placeholder="Ej. 1,250.00"
                    >
                </x-form-field>
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    @if($form->is_adjustment)
                        Se registrará como <span class="font-semibold text-rose-600 dark:text-rose-400">monto negativo</span>.
                    @else
                        Registro normal (monto positivo).
                    @endif
                </p>
            </div>

            <x-form-field label="Tipo de pago" name="form.payment_type">
                <select
                    id="form.payment_type"
                    wire:model.defer="form.payment_type"
                    class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-sm text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
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
            </x-form-field>
        </div>

        {{-- Fecha de pago / estatus --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form-field label="Fecha de pago" name="form.payment_date">
                <input
                    id="form.payment_date"
                    type="date"
                    wire:model.defer="form.payment_date"
                    class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
            </x-form-field>

            <x-form-field label="Estatus del pago" name="form.status">
                <select
                    id="form.status"
                    wire:model.defer="form.status"
                    class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-sm text-gray-900 shadow-sm
                           focus:border-emerald-500 focus:ring-emerald-500
                           dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                >
                    <option value="">Selecciona estatus</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="pagado">Pagado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </x-form-field>
        </div>

        {{-- Observaciones --}}
        <x-form-field label="Observaciones" name="form.notes">
            <textarea
                id="form.notes"
                wire:model.defer="form.notes"
                rows="3"
                class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm
                       focus:border-emerald-500 focus:ring-emerald-500
                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                placeholder="Notas adicionales sobre el pago, acuerdos con el proveedor, etc."
            ></textarea>
        </x-form-field>

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
                <span>{{ $form->supplyId ? 'Guardar cambios' : 'Crear insumo' }}</span>
            </button>
        </div>
    </form>
</x-modal>
