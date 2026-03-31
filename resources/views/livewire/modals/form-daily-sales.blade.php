{{-- MODAL: subir PDF ventas diarias --}}
<x-modal wire:model="open" maxWidth="lg">
    <div
        x-data="{ uploading: false, progress: 0 }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-cancel="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                Subir reporte de ventas
            </h3>
        </div>

        <div class="p-4 space-y-4">
            <div class="grid gap-3 sm:grid-cols-3">
                <x-form-field label="Unidad de negocio" name="form.business_unit">
                    <select
                        id="form.business_unit"
                        wire:model.live="form.business_unit"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                        @foreach(\App\Domain\BusinessUnit::cases() as $bu)
                            <option value="{{ $bu->value }}">{{ $bu->value }}</option>
                        @endforeach
                    </select>
                </x-form-field>

                <x-form-field label="Fecha de operacion" name="form.operation_date">
                    <input
                        id="form.operation_date"
                        type="date"
                        wire:model.live="form.operation_date"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                </x-form-field>

                <x-form-field label="Turno" name="form.turno">
                    <select
                        id="form.turno"
                        wire:model.live="form.turno"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="1">Matutino (7:00 - 15:00)</option>
                        <option value="2">Vespertino (15:00 - 22:00)</option>
                    </select>
                </x-form-field>
            </div>

            <x-form-field label="Archivo PDF" name="form.file">
                <input
                    id="form.file"
                    type="file"
                    wire:model="form.file"
                    accept=".pdf"
                    class="block w-full text-xs text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-emerald-700
                           hover:file:bg-emerald-100 dark:text-gray-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-300"
                >
            </x-form-field>

            {{-- Upload progress --}}
            <div x-show="uploading" x-transition class="mt-2">
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-full rounded-full bg-emerald-600 transition-all dark:bg-emerald-400"
                         :style="'width: ' + progress + '%'"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="progress + '% subido'"></p>
            </div>

            {{-- Livewire processing indicator --}}
            <div wire:loading wire:target="file" class="mt-2">
                <div class="inline-flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400">
                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Procesando archivo…
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
                class="rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50
                       dark:bg-emerald-500 dark:hover:bg-emerald-400"
            >
                <span wire:loading.remove wire:target="uploadPdf">Subir PDF</span>
                <span wire:loading wire:target="uploadPdf">
                    <svg class="mr-1 inline h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Enviando…
                </span>
            </button>
        </div>
    </div>
</x-modal>
