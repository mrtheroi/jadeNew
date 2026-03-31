{{-- MODAL: crear / editar usuario --}}
<x-modal wire:model="open" maxWidth="xl">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ $form->selected_id ? 'Editar usuario' : 'Nuevo usuario' }}
        </h3>
    </div>

    <div class="p-4 space-y-4">
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form-field label="Nombre" name="form.name">
                    <input
                        id="form.name"
                        type="text"
                        wire:model.live="form.name"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        placeholder="Nombre completo"
                    >
                </x-form-field>
            </div>

            <div class="sm:col-span-2">
                <x-form-field label="Email" name="form.email">
                    <input
                        id="form.email"
                        type="email"
                        wire:model.live="form.email"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        placeholder="correo@ejemplo.com"
                    >
                </x-form-field>
            </div>

            <div class="sm:col-span-2">
                <x-form-field label="Rol" name="form.role">
                    <select
                        id="form.role"
                        wire:model.live="form.role"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                               focus:border-emerald-500 focus:ring-emerald-500
                               dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="">Selecciona un rol</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </x-form-field>
            </div>

            <div class="sm:col-span-2">
                <x-form-field label="Password {{ $form->selected_id ? '(dejar vacío para mantener)' : '' }}" name="form.password">
                    <div class="relative" x-data="{ showPassword: false }">
                        <input
                            id="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            wire:model.live="form.password"
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                                   focus:border-emerald-500 focus:ring-emerald-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="Mínimo 8 caracteres"
                        >
                        <button
                            type="button"
                            x-on:click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            tabindex="-1"
                        >
                            {{-- Eye open (password hidden) --}}
                            <svg x-show="!showPassword" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            {{-- Eye closed (password visible) --}}
                            <svg x-show="showPassword" x-cloak class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </x-form-field>
            </div>
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
            wire:click="save"
            wire:loading.attr="disabled"
            class="rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500 transition disabled:opacity-50
                   dark:bg-emerald-500 dark:hover:bg-emerald-400"
        >
            <span wire:loading.remove wire:target="save">{{ $form->selected_id ? 'Guardar cambios' : 'Crear usuario' }}</span>
            <span wire:loading wire:target="save">Guardando…</span>
        </button>
    </div>
</x-modal>
