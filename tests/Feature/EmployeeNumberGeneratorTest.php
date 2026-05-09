<?php

use App\Models\Employee;
use App\Services\EmployeeNumberGenerator;

test('next() returns WYB-0001 when there are no employees', function () {
    $generator = new EmployeeNumberGenerator;

    expect($generator->next())->toBe('WYB-0001');
});

test('next() increments past the latest WYB-XXXX number', function () {
    Employee::factory()->create(['employee_number' => 'WYB-0001']);
    Employee::factory()->create(['employee_number' => 'WYB-0002']);
    Employee::factory()->create(['employee_number' => 'WYB-0042']);

    $generator = new EmployeeNumberGenerator;

    expect($generator->next())->toBe('WYB-0043');
});

test('next() ignores employees whose number does not match WYB-XXXX', function () {
    // Empleados históricos cargados a mano antes de la convención WYB.
    Employee::factory()->create(['employee_number' => 'EMP-9999']);
    Employee::factory()->create(['employee_number' => 'JADE-001']);
    Employee::factory()->create(['employee_number' => 'whatever']);

    $generator = new EmployeeNumberGenerator;

    // Si no hay ningún WYB-XXXX previo, arranca en WYB-0001.
    expect($generator->next())->toBe('WYB-0001');
});

test('next() correctly orders by numeric sequence not string', function () {
    // Si ordenara como string puro, "WYB-0009" sería mayor que "WYB-0010".
    Employee::factory()->create(['employee_number' => 'WYB-0009']);
    Employee::factory()->create(['employee_number' => 'WYB-0010']);

    $generator = new EmployeeNumberGenerator;

    expect($generator->next())->toBe('WYB-0011');
});
