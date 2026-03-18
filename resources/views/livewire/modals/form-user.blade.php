<div>
    <flux:modal wire:model="open" name="store-user" class="md:w-200">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $selected_id > 0 ? 'Editar registro' : 'Crear un nuevo usuario'}}
                </flux:heading>
                {{--<flux:subheading>Make changes to your personal details.</flux:subheading>--}}
            </div>

            <flux:input label="Name" wire:model="name" placeholder="Your name" />
            <flux:error name="name" />

            <flux:input type="password" label="Password" wire:model="password" placeholder="Your password" />
            <flux:error name="password" />

            <flux:input label="Email" wire:model="email" placeholder="Your email" />
            <flux:error name="email" />

            <flux:select label="Rol" wire:model="role">
                <flux:select.option value="" disabled> Selecciona el rol</flux:select.option>
                @foreach($roles as $r)
                    <flux:select.option value="{{ $r->name }}">{{ $r->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="role" />

            <div class="flex">
                <flux:spacer />

                <flux:button type="submit"  wire:click="$set('open', false)">Cancel</flux:button>
                @if($selected_id < 1)
                    <flux:button type="submit" variant="primary" class="ml-2" wire:click.prevent="save">Save changes</flux:button>
                @else
                    <flux:button type="submit" variant="primary" wire:click.prevent="update" class="ml-2">Updated changes</flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
</div>
