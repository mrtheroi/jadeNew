<?php

use App\Livewire\HumanResources\EmployeesController;
use App\Models\Employee;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Roles necesarios para gates.
    Role::firstOrCreate(['name' => 'Super']);
    Role::firstOrCreate(['name' => 'Admin']);
    Role::firstOrCreate(['name' => 'User']);

    $this->superUser = User::factory()->create();
    $this->superUser->assignRole('Super');

    $this->basicUser = User::factory()->create();
    $this->basicUser->assignRole('User');
});

// ─── ENDPOINT: PDF del contrato ──────────────────────────────────────────

test('contract pdf endpoint responds successfully for an existing employee', function () {
    $employee = Employee::factory()->create([
        'business_unit' => 'Jade',
        'full_name' => 'Test Empleado',
        'employee_number' => 'WYB-0007',
    ]);

    $response = $this->actingAs($this->superUser)
        ->get(route('rrhh.empleados.contrato.pdf', $employee->id));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('contract pdf requires authentication', function () {
    $employee = Employee::factory()->create();

    $response = $this->get(route('rrhh.empleados.contrato.pdf', $employee->id));

    $response->assertRedirect();
});

// ─── HELPERS DEL MODELO ──────────────────────────────────────────────────

test('dailySalary divides gross by the right divisor based on salary_period', function () {
    $monthly = Employee::factory()->create(['salary_gross' => 9000, 'salary_period' => 'Mensual']);
    $biweekly = Employee::factory()->create(['salary_gross' => 4500, 'salary_period' => 'Quincenal']);
    $weekly = Employee::factory()->create(['salary_gross' => 2100, 'salary_period' => 'Semanal']);

    expect($monthly->dailySalary())->toBe(300.00);
    expect($biweekly->dailySalary())->toBe(300.00);
    expect($weekly->dailySalary())->toBe(300.00);
});

test('dailySalary returns null when salary_gross or salary_period are missing', function () {
    $noGross = Employee::factory()->create(['salary_gross' => null, 'salary_period' => 'Mensual']);
    $noPeriod = Employee::factory()->create(['salary_gross' => 9000, 'salary_period' => null]);

    expect($noGross->dailySalary())->toBeNull();
    expect($noPeriod->dailySalary())->toBeNull();
});

test('dailySalaryInWords formats amount in cheque mexicano style', function () {
    $employee = Employee::factory()->create(['salary_gross' => 7467.90, 'salary_period' => 'Mensual']);

    // 7467.90 / 30 = 248.93
    $words = $employee->dailySalaryInWords();

    expect($words)->toContain('93/100');
    // Formato exacto: "(DOSCIENTOS CUARENTA Y OCHO 93/100)" — verificamos partes
    expect($words)->toStartWith('(');
    expect($words)->toEndWith(')');
});

test('companyData reads from config based on business_unit', function () {
    $jadeEmp = Employee::factory()->create(['business_unit' => 'Jade']);
    $kinEmp = Employee::factory()->create(['business_unit' => 'KIN']);

    expect($jadeEmp->companyData()['legal_name'])->toBe(config('company.units.Jade.legal_name'));
    expect($kinEmp->companyData()['legal_name'])->toBe(config('company.units.KIN.legal_name'));
});

// ─── GATE: privacidad del salario ────────────────────────────────────────

test('view-salary gate allows Super and Admin only', function () {
    expect($this->superUser->can('view-salary'))->toBeTrue();
    expect($this->basicUser->can('view-salary'))->toBeFalse();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    expect($admin->can('view-salary'))->toBeTrue();
});

test('save discards salary fields when user lacks view-salary gate', function () {
    Livewire::actingAs($this->basicUser)
        ->test(EmployeesController::class)
        ->call('create')
        ->set('form.full_name', 'Sin permiso')
        ->set('form.business_unit', 'Jade')
        ->set('form.nationality', 'Mexicana')
        ->set('form.children_count', 0)
        ->set('form.salary_gross', '9000')
        ->set('form.salary_net', '7800')
        ->set('form.salary_period', 'Mensual')
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::where('full_name', 'Sin permiso')->first();

    expect($employee->salary_gross)->toBeNull();
    expect($employee->salary_net)->toBeNull();
    expect($employee->salary_period)->toBeNull();
});

test('save persists salary fields when user has view-salary gate', function () {
    Livewire::actingAs($this->superUser)
        ->test(EmployeesController::class)
        ->call('create')
        ->set('form.full_name', 'Con permiso')
        ->set('form.business_unit', 'Jade')
        ->set('form.nationality', 'Mexicana')
        ->set('form.children_count', 0)
        ->set('form.salary_gross', '9000')
        ->set('form.salary_net', '7800')
        ->set('form.salary_period', 'Mensual')
        ->call('save')
        ->assertHasNoErrors();

    $employee = Employee::where('full_name', 'Con permiso')->first();

    expect((float) $employee->salary_gross)->toBe(9000.00);
    expect((float) $employee->salary_net)->toBe(7800.00);
    expect($employee->salary_period)->toBe('Mensual');
});
