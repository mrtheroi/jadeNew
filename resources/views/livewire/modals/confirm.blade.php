<div>
    @if($show)
        <div
            x-data="{ show: @entangle('show'), confirmation: '' }"
            x-show="show"
            x-cloak
            class="relative z-50"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
                aria-hidden="true"
                x-show="show"
                x-transition.opacity
            ></div>

            {{-- Panel --}}
            <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
                <div
                    class="w-full max-w-lg transform overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-black/10
                           transition-all dark:bg-gray-900 dark:ring-white/10"
                    x-show="show"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
                >
                    {{-- Header --}}
                    <div class="flex items-start gap-4 border-b border-gray-100 px-6 py-5 dark:border-white/10">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-red-50 ring-1 ring-red-600/10 dark:bg-red-900/20 dark:ring-red-500/20">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 id="modal-title" class="text-sm font-semibold text-gray-900 dark:text-white">
                                Eliminar registro permanentemente
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                Esta acción eliminará el registro de forma <span class="font-semibold text-red-600 dark:text-red-400">irreversible</span>.
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Para continuar, escribe <span class="font-semibold">CONFIRMAR</span>.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="show = false"
                            class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        >
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                            Confirmación
                        </label>
                        <div class="mt-1 relative">
                            <input
                                type="text"
                                x-model="confirmation"
                                placeholder="CONFIRMAR"
                                class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm
                                       focus:border-red-500 focus:ring-red-500
                                       dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                                @keydown.enter.prevent
                            />

                            {{-- Hint --}}
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Escribe exactamente <span class="font-semibold">CONFIRMAR</span> para habilitar el botón.
                            </p>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 px-6 py-4 sm:flex-row sm:justify-end dark:border-white/10">
                        <button
                            type="button"
                            @click="show = false"
                            class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm
                                   hover:bg-gray-50 dark:border-white/15 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="confirmDelete"
                            :disabled="confirmation !== 'CONFIRMAR'"
                            :class="confirmation === 'CONFIRMAR'
                                ? 'bg-red-600 hover:bg-red-500 focus-visible:outline-red-600'
                                : 'bg-gray-200 text-gray-500 cursor-not-allowed dark:bg-gray-800 dark:text-gray-500'"
                            class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm
                                   focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                        >
                            Eliminar definitivamente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
