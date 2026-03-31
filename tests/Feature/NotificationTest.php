<?php

use App\Livewire\Notification;
use Livewire\Livewire;

it('dispatches toast browser event on notify', function () {
    Livewire::test(Notification::class)
        ->call('showNotification', 'Registro guardado', 'success')
        ->assertDispatched('toast', message: 'Registro guardado', type: 'success');
});

it('dispatches toast with error type', function () {
    Livewire::test(Notification::class)
        ->call('showNotification', 'Algo salió mal', 'error')
        ->assertDispatched('toast', message: 'Algo salió mal', type: 'error');
});

it('dispatches toast with warning type', function () {
    Livewire::test(Notification::class)
        ->call('showNotification', 'Cuidado con esto', 'warning')
        ->assertDispatched('toast', message: 'Cuidado con esto', type: 'warning');
});

it('dispatches toast with info type', function () {
    Livewire::test(Notification::class)
        ->call('showNotification', 'Información importante', 'info')
        ->assertDispatched('toast', message: 'Información importante', type: 'info');
});

it('defaults to success type when no type provided', function () {
    Livewire::test(Notification::class)
        ->call('showNotification', 'Operación completada')
        ->assertDispatched('toast', message: 'Operación completada', type: 'success');
});
