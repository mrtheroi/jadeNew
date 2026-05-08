<?php

use App\Application\HumanResources\Employees\EmployeesQuery;
use App\Livewire\HumanResources\EmployeesController;
use App\Models\Employee;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->query = app(EmployeesQuery::class);
});

// ─── MODELO ──────────────────────────────────────────────────────────────

test('age accessor calculates from birth_date', function () {
    $employee = Employee::factory()->create([
        'birth_date' => now()->subYears(30)->subMonths(2),
    ]);

    expect($employee->age)->toBe(30);
});

test('age accessor returns null when birth_date is null', function () {
    $employee = Employee::factory()->create(['birth_date' => null]);

    expect($employee->age)->toBeNull();
});

test('active scope filters only active employees', function () {
    Employee::factory()->count(2)->create(['is_active' => true]);
    Employee::factory()->inactive()->count(3)->create();

    expect(Employee::active()->count())->toBe(2);
    expect(Employee::inactive()->count())->toBe(3);
});

// ─── BACKEND: filtros de la query ────────────────────────────────────────

test('base query filters by business_unit', function () {
    Employee::factory()->forUnit('Jade')->count(3)->create();
    Employee::factory()->forUnit('KIN')->count(2)->create();

    $jade = $this->query->base(['business_unit' => 'Jade'])->get();
    $kin = $this->query->base(['business_unit' => 'KIN'])->get();

    expect($jade)->toHaveCount(3);
    expect($kin)->toHaveCount(2);
});

test('base query filters by status active', function () {
    Employee::factory()->count(4)->create(['is_active' => true]);
    Employee::factory()->inactive()->count(2)->create();

    $result = $this->query->base(['status' => 'active'])->get();

    expect($result)->toHaveCount(4);
});

test('base query filters by status inactive', function () {
    Employee::factory()->count(4)->create(['is_active' => true]);
    Employee::factory()->inactive()->count(2)->create();

    $result = $this->query->base(['status' => 'inactive'])->get();

    expect($result)->toHaveCount(2);
});

test('base query searches by full_name', function () {
    Employee::factory()->create(['full_name' => 'Juan Perez Garcia']);
    Employee::factory()->create(['full_name' => 'Maria Lopez']);
    Employee::factory()->create(['full_name' => 'Pedro Ruiz']);

    $result = $this->query->base(['search' => 'Perez'])->get();

    expect($result)->toHaveCount(1);
    expect($result->first()->full_name)->toBe('Juan Perez Garcia');
});

test('base query searches by employee_number', function () {
    Employee::factory()->create(['employee_number' => 'EMP-1001']);
    Employee::factory()->create(['employee_number' => 'EMP-2002']);

    $result = $this->query->base(['search' => '1001'])->get();

    expect($result)->toHaveCount(1);
    expect($result->first()->employee_number)->toBe('EMP-1001');
});

test('totalActive ignores inactive employees', function () {
    Employee::factory()->forUnit('Jade')->count(3)->create();
    Employee::factory()->forUnit('Jade')->inactive()->count(2)->create();

    expect($this->query->totalActive(['business_unit' => 'Jade']))->toBe(3);
});

test('totalsByUnit groups active employees by business_unit', function () {
    Employee::factory()->forUnit('Jade')->count(3)->create();
    Employee::factory()->forUnit('KIN')->count(2)->create();
    Employee::factory()->forUnit('KIN')->inactive()->count(1)->create();

    $totals = $this->query->totalsByUnit([])->keyBy('business_unit');

    expect((int) $totals['Jade']->total)->toBe(3);
    expect((int) $totals['KIN']->total)->toBe(2);
});

// ─── LIVEWIRE: CRUD ──────────────────────────────────────────────────────

test('can create an employee from the controller', function () {
    Livewire::actingAs($this->user)
        ->test(EmployeesController::class)
        ->call('create')
        ->set('form.employee_number', 'EMP-9999')
        ->set('form.full_name', 'Nuevo Empleado')
        ->set('form.business_unit', 'Jade')
        ->set('form.nationality', 'Mexicana')
        ->set('form.children_count', 0)
        ->call('save')
        ->assertHasNoErrors();

    expect(Employee::where('employee_number', 'EMP-9999')->exists())->toBeTrue();
});

test('cannot create employee with duplicated employee_number', function () {
    Employee::factory()->create(['employee_number' => 'EMP-DUP']);

    Livewire::actingAs($this->user)
        ->test(EmployeesController::class)
        ->call('create')
        ->set('form.employee_number', 'EMP-DUP')
        ->set('form.full_name', 'Otro Empleado')
        ->set('form.business_unit', 'Jade')
        ->set('form.nationality', 'Mexicana')
        ->set('form.children_count', 0)
        ->call('save')
        ->assertHasErrors(['form.employee_number']);
});

test('business_unit is required on create', function () {
    Livewire::actingAs($this->user)
        ->test(EmployeesController::class)
        ->call('create')
        ->set('form.employee_number', 'EMP-7777')
        ->set('form.full_name', 'Sin Unidad')
        ->set('form.business_unit', '')
        ->set('form.nationality', 'Mexicana')
        ->set('form.children_count', 0)
        ->call('save')
        ->assertHasErrors(['form.business_unit']);
});

test('terminated_at is required when is_active is false', function () {
    Livewire::actingAs($this->user)
        ->test(EmployeesController::class)
        ->call('create')
        ->set('form.employee_number', 'EMP-BAJA')
        ->set('form.full_name', 'Baja Sin Fecha')
        ->set('form.business_unit', 'Jade')
        ->set('form.nationality', 'Mexicana')
        ->set('form.children_count', 0)
        ->set('form.is_active', false)
        ->set('form.terminated_at', null)
        ->call('save')
        ->assertHasErrors(['form.terminated_at']);
});

test('saving as active clears any terminated_at', function () {
    $employee = Employee::factory()->inactive()->create();

    Livewire::actingAs($this->user)
        ->test(EmployeesController::class)
        ->call('edit', $employee->id)
        ->set('form.is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $employee->refresh();
    expect($employee->is_active)->toBeTrue();
    expect($employee->terminated_at)->toBeNull();
});

test('can edit an existing employee', function () {
    $employee = Employee::factory()->create(['full_name' => 'Original']);

    Livewire::actingAs($this->user)
        ->test(EmployeesController::class)
        ->call('edit', $employee->id)
        ->set('form.full_name', 'Modificado')
        ->call('save')
        ->assertHasNoErrors();

    expect($employee->fresh()->full_name)->toBe('Modificado');
});

test('destroy removes the employee', function () {
    $employee = Employee::factory()->create();

    Livewire::actingAs($this->user)
        ->test(EmployeesController::class)
        ->call('destroy', $employee->id);

    expect(Employee::find($employee->id))->toBeNull();
});

// ─── LIVEWIRE: filtros y reset ───────────────────────────────────────────

test('resetFilters clears search, business_unit and status', function () {
    Livewire::actingAs($this->user)
        ->test(EmployeesController::class)
        ->set('search', 'Juan')
        ->set('business_unit', 'Jade')
        ->set('status', 'inactive')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('business_unit', '')
        ->assertSet('status', 'active');
});
