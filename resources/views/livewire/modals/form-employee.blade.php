{{-- MODAL: crear / editar empleado --}}
<x-modal wire:model="open" maxWidth="2xl">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ $form->isEditing() ? 'Editar empleado' : 'Nuevo empleado' }}
        </h3>
    </div>

    <div class="p-4 space-y-6 max-h-[75vh] overflow-y-auto">
        {{-- IDENTIFICACIÓN --}}
        <div>
            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                <i class="fa-thin fa-id-card mr-1"></i> Identificación
            </h4>
            <div class="grid gap-3 sm:grid-cols-2">
                <x-form-field label="No. de empleado *" name="form.employee_number">
                    <input type="text" wire:model="form.employee_number" placeholder="EMP-1234"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Nombre completo *" name="form.full_name">
                    <input type="text" wire:model="form.full_name" placeholder="Juan Pérez García"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Email" name="form.email">
                    <input type="email" wire:model="form.email" placeholder="correo@ejemplo.com"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Teléfono celular" name="form.phone">
                    <input type="tel" wire:model="form.phone" placeholder="55 1234 5678"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <div class="sm:col-span-2">
                    <x-form-field label="CURP" name="form.curp">
                        <input type="text" wire:model="form.curp" maxlength="18" placeholder="18 caracteres"
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs uppercase text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                    </x-form-field>
                </div>
            </div>
        </div>

        {{-- DATOS PERSONALES --}}
        <div class="border-t border-gray-200 pt-4 dark:border-white/10">
            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                <i class="fa-thin fa-user mr-1"></i> Datos personales
            </h4>
            <div class="grid gap-3 sm:grid-cols-2">
                <x-form-field label="Fecha de nacimiento" name="form.birth_date">
                    <input type="date" wire:model="form.birth_date"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Lugar de nacimiento" name="form.birth_place">
                    <input type="text" wire:model="form.birth_place" placeholder="Ciudad, Estado"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Género" name="form.gender">
                    <select wire:model="form.gender"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Selecciona…</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </x-form-field>

                <x-form-field label="Estado civil" name="form.marital_status">
                    <select wire:model="form.marital_status"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Selecciona…</option>
                        <option value="Soltero">Soltero</option>
                        <option value="Casado">Casado</option>
                        <option value="Union libre">Unión libre</option>
                        <option value="Divorciado">Divorciado</option>
                        <option value="Viudo">Viudo</option>
                    </select>
                </x-form-field>

                <x-form-field label="Nacionalidad *" name="form.nationality">
                    <input type="text" wire:model="form.nationality"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Cantidad de hijos" name="form.children_count">
                    <input type="number" wire:model="form.children_count" min="0" max="99"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <div class="sm:col-span-2">
                    <x-form-field label="Dirección" name="form.address">
                        <textarea wire:model="form.address" rows="2" placeholder="Calle, número, colonia, CP, ciudad, estado"
                            class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100"></textarea>
                    </x-form-field>
                </div>
            </div>
        </div>

        {{-- DATOS LABORALES --}}
        <div class="border-t border-gray-200 pt-4 dark:border-white/10">
            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                <i class="fa-thin fa-briefcase mr-1"></i> Datos laborales
            </h4>
            <div class="grid gap-3 sm:grid-cols-2">
                <x-form-field label="Unidad de negocio *" name="form.business_unit">
                    <select wire:model="form.business_unit"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Selecciona…</option>
                        @foreach(\App\Domain\BusinessUnit::cases() as $bu)
                            <option value="{{ $bu->value }}">{{ $bu->value }}</option>
                        @endforeach
                    </select>
                </x-form-field>

                <x-form-field label="Departamento" name="form.department">
                    <input type="text" wire:model="form.department" placeholder="Cocina, Servicio, Caja…"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Gerente" name="form.manager_name">
                    <input type="text" wire:model="form.manager_name" placeholder="Nombre del gerente directo"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Fecha de ingreso" name="form.hired_at">
                    <input type="date" wire:model="form.hired_at"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 text-xs font-medium text-gray-700 dark:text-gray-200">
                        <input type="checkbox" wire:model.live="form.is_active"
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-white/20 dark:bg-gray-900">
                        Empleado activo
                    </label>
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                        Desmarcá esta casilla si el empleado fue dado de baja.
                    </p>
                </div>

                @if(! $form->is_active)
                    <div class="sm:col-span-2">
                        <x-form-field label="Fecha de baja *" name="form.terminated_at">
                            <input type="date" wire:model="form.terminated_at"
                                class="block w-full rounded-md border border-rose-300 bg-rose-50/30 py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-rose-500 focus:ring-rose-500 dark:border-rose-500/40 dark:bg-rose-900/10 dark:text-gray-100">
                        </x-form-field>
                    </div>
                @endif
            </div>
        </div>

        {{-- CONTACTO DE EMERGENCIA --}}
        <div class="border-t border-gray-200 pt-4 dark:border-white/10">
            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                <i class="fa-thin fa-phone mr-1"></i> Contacto de emergencia
            </h4>
            <div class="grid gap-3 sm:grid-cols-3">
                <x-form-field label="Nombre" name="form.emergency_contact_name">
                    <input type="text" wire:model="form.emergency_contact_name"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Teléfono" name="form.emergency_contact_phone">
                    <input type="tel" wire:model="form.emergency_contact_phone"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                </x-form-field>

                <x-form-field label="Parentesco" name="form.emergency_contact_relationship">
                    <select wire:model="form.emergency_contact_relationship"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-xs text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/15 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Selecciona…</option>
                        <option value="Padre">Padre</option>
                        <option value="Madre">Madre</option>
                        <option value="Hermano(a)">Hermano(a)</option>
                        <option value="Conyuge">Cónyuge</option>
                        <option value="Hijo(a)">Hijo(a)</option>
                        <option value="Otro">Otro</option>
                    </select>
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
            <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Guardar cambios' : 'Crear empleado' }}</span>
            <span wire:loading wire:target="save">Guardando…</span>
        </button>
    </div>
</x-modal>
