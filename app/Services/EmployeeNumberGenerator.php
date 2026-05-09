<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeNumberGenerator
{
    public const PREFIX = 'WYB-';

    public const PADDING = 4;

    /**
     * Devuelve el siguiente número de empleado en formato WYB-XXXX.
     *
     * Ignora cualquier employee_number histórico que no matchee el prefijo
     * (los empleados cargados a mano antes de esta convención conservan
     * su número original; el contador arranca en WYB-0001 si no hay
     * ningún WYB-XXXX previo).
     */
    public function next(): string
    {
        $latest = Employee::query()
            ->where('employee_number', 'like', self::PREFIX.'%')
            ->orderByRaw('LENGTH(employee_number) DESC, employee_number DESC')
            ->value('employee_number');

        $sequence = 1;

        if ($latest !== null) {
            $current = (int) substr($latest, strlen(self::PREFIX));
            $sequence = $current + 1;
        }

        return self::PREFIX.str_pad((string) $sequence, self::PADDING, '0', STR_PAD_LEFT);
    }
}
