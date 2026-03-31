{{-- MODAL: crear / editar tipo de gasto --}}
<x-modal wire:model="open" maxWidth="lg">
    {{-- Header modal --}}
    <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4 dark:border-white/10">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $form->expenseTypeId ? 'Editar tipo de gasto' : 'Nuevo tipo de gasto' }}
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Tipo de gasto.
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    <form wire:submit.prevent="save" class="space-y-5 px-6 py-5">
        {{-- Nombre del gasto --}}
        <x-form-field label="Tipo del Gasto" name="form.expense_type_name">
            <input
                id="form.expense_type_name"
                type="text"
                wire:model.defer="form.expense_type_name"
                class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm
                       focus:border-emerald-500 focus:ring-emerald-500
                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                placeholder="Ej. Mano de Obra, Gastos Operativos.."
            >
        </x-form-field>

        {{-- Estado --}}
        <x-form-field label="Estado" name="form.is_active">
            <select
                id="form.is_active"
                wire:model.defer="form.is_active"
                class="block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-sm text-gray-900 shadow-sm
                       focus:border-emerald-500 focus:ring-emerald-500
                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
            >
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
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
                <span>{{ $form->expenseTypeId ? 'Guardar cambios' : 'Crear tipo de gasto' }}</span>
            </button>
        </div>
    </form>
</x-modal>
