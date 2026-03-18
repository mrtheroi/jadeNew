{{-- MODAL: subir PDF ventas diarias --}}
<div
    x-data="{ uploading: false, progress: 0 }"
    x-on:livewire-upload-start="uploading = true"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-cancel="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    x-show="$wire.open"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    @click.self="$wire.closeModal()"
>
    <div class="w-full max-w-lg rounded-xl bg-white shadow-lg dark:bg-gray-900" x-show="$wire.open" x-transition>
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                Subir reporte de ventas
            </h3>
            <button
                type="button"
                wire:click="closeModal"
                class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition
                       dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                aria-label="Cerrar"
            >
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-4 space-y-4">
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Unidad de negocio</label>
                    <select
                        wire:model.live="business_unit"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="Jade">Jade</option>
                        <option value="Fuego Ambar">Fuego Ambar</option>
                        <option value="KIN">KIN</option>
                    </select>
                    @error('business_unit') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Fecha de operacion</label>
                    <input
                        type="date"
                        wire:model.live="operation_date"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                    @error('operation_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Turno</label>
                    <select
                        wire:model.live="turno"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="1">Matutino (7:00 - 15:00)</option>
                        <option value="2">Vespertino (15:00 - 22:00)</option>
                    </select>
                    @error('turno') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Archivo PDF</label>
                <input
                    type="file"
                    wire:model="file"
                    accept=".pdf"
                    class="mt-1 block w-full text-xs text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-indigo-700
                           hover:file:bg-indigo-100 dark:text-gray-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300"
                >
                @error('file') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

                {{-- Upload progress --}}
                <div x-show="uploading" x-transition class="mt-2">
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-indigo-600 transition-all dark:bg-indigo-400"
                             :style="'width: ' + progress + '%'"></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="progress + '% subido'"></p>
                </div>

                {{-- Livewire processing indicator --}}
                <div wire:loading wire:target="file" class="mt-2">
                    <div class="inline-flex items-center gap-2 text-xs text-indigo-600 dark:text-indigo-400">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        Procesando archivo…
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20">
                <p class="text-xs text-amber-800 dark:text-amber-200">
                    <svg class="mr-1 inline h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    Sube el reporte PDF de <strong>SoftRestaurant</strong>.
                    El sistema extraera automaticamente los datos de ventas, metodos de pago y propinas.
                    Solo se permiten <strong>2 archivos por dia</strong> (uno por turno).
                </p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-white/10">
            <button
                type="button"
                wire:click="closeModal"
                class="rounded-md border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition
                       dark:border-white/10 dark:text-gray-100 dark:hover:bg-white/5"
            >
                Cancelar
            </button>

            <button
                type="button"
                wire:click="uploadPdf"
                wire:loading.attr="disabled"
                class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition disabled:opacity-50
                       dark:bg-indigo-500 dark:hover:bg-indigo-400"
            >
                <span wire:loading.remove wire:target="uploadPdf">Subir PDF</span>
                <span wire:loading wire:target="uploadPdf">
                    <svg class="mr-1 inline h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Enviando…
                </span>
            </button>
        </div>
    </div>
</div>
