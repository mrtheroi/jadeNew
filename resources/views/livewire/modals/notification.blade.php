<div>
    @if($visible)
        <div
            x-data="{ show: @entangle('visible') }"
            x-show="show"
            x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-0 scale-95"
            x-init="setTimeout(() => show = false, 3000)"
            class="fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 pointer-events-none z-50"
        >
            <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
                <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white/90 backdrop-blur shadow-lg ring-1 ring-black/5">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="shrink-0">
                                <svg class="size-6 text-emerald-500" viewBox="0 0 24 24" fill="none"
                                     stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12.75 11.25 15 15 9.75" />
                                </svg>
                            </div>

                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $message }}
                                </p>
                            </div>

                            <div class="ml-4 flex shrink-0">
                                <button
                                    type="button"
                                    @click="show = false"
                                    class="inline-flex rounded-md bg-white/0 text-gray-400 hover:text-gray-600
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <span class="sr-only">Cerrar</span>
                                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path
                                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif
</div>
