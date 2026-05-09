<?php

use App\Http\Controllers\DashboardExportController;
use App\Http\Controllers\EmployeeContractPdfController;
use App\Http\Controllers\PurchaseOrderPdfController;
use App\Livewire\Expenses\CategoryController;
use App\Livewire\Expenses\ExpenseTypeController;
use App\Livewire\Expenses\PurchaseOrdersController;
use App\Livewire\Expenses\SuppliesController;
use App\Livewire\HumanResources\EmployeesController;
use App\Livewire\Sales\DailySalesController;
use App\Livewire\Sales\SalesDashboard;
use App\Livewire\Users\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('livewire.auth.login');
})->name('home');

// Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');

Route::get('/dashboard/ventas/export/excel', [DashboardExportController::class, 'excel'])
    ->name('dashboard.ventas.export.excel')
    ->middleware(['auth']);

Route::get('/dashboard/ventas/export/pdf', [DashboardExportController::class, 'pdf'])
    ->name('dashboard.ventas.export.pdf')
    ->middleware(['auth']);

Route::get('/dashboard/estado-resultados/export/excel', [DashboardExportController::class, 'estadoResultadosExcel'])
    ->name('dashboard.estado-resultados.export.excel')
    ->middleware(['auth']);

Route::get('/dashboard/estado-resultados/export/pdf', [DashboardExportController::class, 'estadoResultadosPdf'])
    ->name('dashboard.estado-resultados.export.pdf')
    ->middleware(['auth']);

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', SalesDashboard::class)->name('dashboard');

    Route::get('users', UserController::class)->name('users');
    Route::get('ventas', DailySalesController::class)->name('ventas');
    Route::get('categories', CategoryController::class)->name('categories');
    Route::get('supplies', SuppliesController::class)->name('supplies');

    Route::get('expense-types', ExpenseTypeController::class)->name('expense-types');

    // Órdenes de Compra (postmortem por día y unidad)
    Route::get('ordenes-compra', PurchaseOrdersController::class)->name('ordenes-compra');
    Route::get('ordenes-compra/{purchaseOrder}/pdf', [PurchaseOrderPdfController::class, 'show'])
        ->name('ordenes-compra.pdf');

    // Recursos Humanos
    Route::get('rrhh/empleados', EmployeesController::class)->name('rrhh.empleados');
    Route::get('rrhh/empleados/{employee}/contrato/pdf', [EmployeeContractPdfController::class, 'show'])
        ->name('rrhh.empleados.contrato.pdf');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
