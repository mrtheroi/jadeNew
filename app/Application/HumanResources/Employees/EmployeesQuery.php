<?php

namespace App\Application\HumanResources\Employees;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmployeesQuery
{
    /**
     * Base query para la tabla de empleados.
     *
     * Filters esperados:
     * - search (string|null) — busca en full_name, email, employee_number, curp
     * - business_unit (string|null)
     * - status (active|inactive|all|null) — default 'all'
     */
    public function base(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $businessUnit = $filters['business_unit'] ?? null;
        $status = $filters['status'] ?? null;

        $q = Employee::query()
            ->orderByDesc('is_active')
            ->orderBy('full_name');

        if ($businessUnit) {
            $q->where('business_unit', $businessUnit);
        }

        if ($status === 'active') {
            $q->where('is_active', true);
        } elseif ($status === 'inactive') {
            $q->where('is_active', false);
        }

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('curp', 'like', "%{$search}%");
            });
        }

        return $q;
    }

    /**
     * Total de empleados activos del filtro vigente.
     * Para la card principal del listado.
     */
    public function totalActive(array $filters): int
    {
        return (int) $this->countBase($filters)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Total de empleados inactivos del filtro vigente (para card aparte).
     */
    public function totalInactive(array $filters): int
    {
        return (int) $this->countBase($filters)
            ->where('is_active', false)
            ->count();
    }

    /**
     * Breakdown por unidad de negocio (solo activos).
     *
     * @return Collection<int, object{business_unit: string, total: int}>
     */
    public function totalsByUnit(array $filters): Collection
    {
        return $this->countBase($filters)
            ->where('is_active', true)
            ->selectRaw('business_unit, COUNT(*) as total')
            ->groupBy('business_unit')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Query base para conteos: aplica búsqueda y unidad, NO el filtro de status
     * (cada método de conteo decide qué status incluir).
     */
    private function countBase(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $businessUnit = $filters['business_unit'] ?? null;

        $q = Employee::query();

        if ($businessUnit) {
            $q->where('business_unit', $businessUnit);
        }

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('curp', 'like', "%{$search}%");
            });
        }

        return $q;
    }
}
