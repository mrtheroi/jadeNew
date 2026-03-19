{{-- MODAL: crear / editar usuario --}}
@if($open)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeModal">
        <div class="w-full max-w-xl rounded-xl bg-white shadow-lg dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $selected_id ? 'Editar usuario' : 'Nuevo usuario' }}
                </h3>
                <button
                    type="button"
                    wire:click="closeModal"
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition
                           dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                    aria-label="Cerrar"
                >
                    <i class="fa-thin fa-xmark fa-fw"></i>
                </button>
            </div>

            <div class="p-4 space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Nombre</label>
                        <input
                            type="text"
                            wire:model.live="name"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="Nombre completo"
                        >
                        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Email</label>
                        <input
                            type="email"
                            wire:model.live="email"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="correo@ejemplo.com"
                        >
                        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Rol</label>
                        <select
                            wire:model.live="role"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                        >
                            <option value="">Selecciona un rol</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                            Password {{ $selected_id ? '(dejar vacío para mantener)' : '' }}
                        </label>
                        <input
                            type="password"
                            wire:model.live="password"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500
                                   dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="Mínimo 8 caracteres"
                        >
                        @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
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
                    class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500 transition disabled:opacity-50
                           dark:bg-indigo-500 dark:hover:bg-indigo-400"
                >
                    <span wire:loading.remove wire:target="save">{{ $selected_id ? 'Guardar cambios' : 'Crear usuario' }}</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
            </div>
        </div>
    </div>
@endif