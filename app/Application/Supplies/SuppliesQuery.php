<?php

namespace App\Application\Supplies;

use App\Models\Category;
use App\Models\ExpenseType;
use App\Models\IncomePeriod;
use App\Models\Supply;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SuppliesQuery
{
    /**
     * Base query para la tabla de supplies.
     * NO excluye cancelados — la tabla los muestra para visibilidad.
     *
     * Filters esperados:
     * - search (string|null)
     * - business_unit (string|null)
     * - expense_type_id (int|string|null)
     * - category_id (int|string|null)
     * - date_from (Y-m-d|null)
     * - date_to (Y-m-d|null)
     */
    public function base(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $businessUnit = $filters['business_unit'] ?? null;
        $expenseTypeId = $filters['expense_type_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;

        $q = Supply::query()
            ->with(['category.expenseType', 'purchaseOrder'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if ($categoryId) {
            $q->where('category_id', $categoryId);
        }

        if ($businessUnit || $expenseTypeId) {
            $q->whereHas('category', function (Builder $cq) use ($businessUnit, $expenseTypeId) {
                if ($businessUnit) {
                    $cq->where('business_unit', $businessUnit);
                }
                if ($expenseTypeId) {
                    $cq->where('expense_type_id', $expenseTypeId);
                }
            });
        }

        if ($from) {
            $q->whereDate('payment_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('payment_date', '<=', $to);
        }

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->whereHas('category', function (Builder $cq) use ($search) {
                    $cq->where('expense_name', 'like', "%{$search}%")
                        ->orWhere('provider_name', 'like', "%{$search}%")
                        ->orWhereHas('expenseType', function (Builder $etq) use ($search) {
                            $etq->where('expense_type_name', 'like', "%{$search}%");
                        });
                })
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('payment_type', 'like', "%{$search}%");
            });
        }

        return $q;
    }

    /**
     * Total general del filtro vigente, EXCLUYENDO cancelados.
     * Es lo que se muestra en la card de TOTAL GENERAL.
     */
    public function totalGeneral(array $filters): float
    {
        return (float) $this->totalsBase($filters)
            ->where('supplies.status', '!=', 'cancelado')
            ->sum('supplies.amount');
    }

    /**
     * Breakdown por tipo de gasto, EXCLUYENDO cancelados.
     * Suma de las filas = totalGeneral.
     *
     * @return Collection<int, object{expense_type_id: int, expense_type_name: string, total_amount: float}>
     */
    public function totalsByExpenseType(array $filters): Collection
    {
        return $this->totalsBase($filters)
            ->join('expense_types', 'categories.expense_type_id', '=', 'expense_types.id')
            ->where('supplies.status', '!=', 'cancelado')
            ->selectRaw('expense_types.id as expense_type_id, expense_types.expense_type_name, SUM(supplies.amount) as total_amount')
            ->groupBy('expense_types.id', 'expense_types.expense_type_name')
            ->orderByDesc('total_amount')
            ->get();
    }

    /**
     * Total de cancelados del filtro vigente, para card aparte.
     */
    public function cancelledTotal(array $filters): float
    {
        return (float) $this->totalsBase($filters)
            ->where('supplies.status', '=', 'cancelado')
            ->sum('supplies.amount');
    }

    /**
     * Cantidad de cancelados del filtro vigente (para mostrar en la card).
     */
    public function cancelledCount(array $filters): int
    {
        return (int) $this->totalsBase($filters)
            ->where('supplies.status', '=', 'cancelado')
            ->count('supplies.id');
    }

    /**
     * Lista de tipos de gasto disponibles para el dropdown.
     * Solo trae tipos que TIENEN registros con los filtros vigentes (cascada total).
     * El $selectedId se incluye siempre para que la selección actual no desaparezca.
     *
     * @return Collection<int, ExpenseType>
     */
    public function expenseTypesForFilter(array $filters, int|string|null $selectedId = null): Collection
    {
        $matchingIds = $this->totalsBase($filters)
            ->select('categories.expense_type_id')
            ->groupBy('categories.expense_type_id');

        return ExpenseType::query()
            ->where(function (Builder $w) use ($matchingIds, $selectedId) {
                $w->whereIn('id', $matchingIds);
                if ($selectedId) {
                    $w->orWhere('id', $selectedId);
                }
            })
            ->orderBy('expense_type_name')
            ->get();
    }

    /**
     * Lista de categorías disponibles para el dropdown, en cascada total.
     * Solo trae categorías que TIENEN registros con los filtros vigentes.
     * El $selectedId se incluye siempre para que la selección actual no desaparezca.
     *
     * @return Collection<int, Category>
     */
    public function categoriesForFilter(array $filters, int|string|null $selectedId = null): Collection
    {
        $matchingIds = $this->totalsBase($filters)
            ->select('supplies.category_id')
            ->groupBy('supplies.category_id');

        return Category::query()
            ->with('expenseType')
            ->where(function (Builder $w) use ($matchingIds, $selectedId) {
                $w->whereIn('id', $matchingIds);
                if ($selectedId) {
                    $w->orWhere('id', $selectedId);
                }
            })
            ->orderBy('expense_name')
            ->get();
    }

    public function incomePeriod(string $businessUnit, string $periodKey): ?IncomePeriod
    {
        return IncomePeriod::query()
            ->where('business_unit', $businessUnit)
            ->where('period_key', $periodKey)
            ->first();
    }

    public function searchCategories(string $term, ?string $businessUnit = null)
    {
        $term = strtoupper(trim($term));

        return Category::query()
            ->with('expenseType')
            ->when($businessUnit, fn ($q) => $q->where('business_unit', $businessUnit))
            ->where(function ($q) use ($term) {
                $q->where('expense_name', 'like', "%{$term}%")
                    ->orWhere('provider_name', 'like', "%{$term}%")
                    ->orWhereHas('expenseType', function ($q2) use ($term) {
                        $q2->where('expense_type_name', 'like', "%{$term}%");
                    });
            })
            ->orderBy('expense_name')
            ->limit(15)
            ->get();
    }

    /**
     * Query base con joins (categories) para los métodos de cards/totales.
     * Aplica los filtros comunes EXCEPTO el de status (cada método decide).
     */
    private function totalsBase(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $businessUnit = $filters['business_unit'] ?? null;
        $expenseTypeId = $filters['expense_type_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;

        $q = Supply::query()
            ->join('categories', 'supplies.category_id', '=', 'categories.id');

        if ($businessUnit) {
            $q->where('categories.business_unit', $businessUnit);
        }

        if ($expenseTypeId) {
            $q->where('categories.expense_type_id', $expenseTypeId);
        }

        if ($categoryId) {
            $q->where('supplies.category_id', $categoryId);
        }

        if ($from) {
            $q->whereDate('supplies.payment_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('supplies.payment_date', '<=', $to);
        }

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->where('categories.expense_name', 'like', "%{$search}%")
                    ->orWhere('categories.provider_name', 'like', "%{$search}%")
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select('id')
                            ->from('expense_types')
                            ->whereColumn('expense_types.id', 'categories.expense_type_id')
                            ->where('expense_types.expense_type_name', 'like', "%{$search}%");
                    })
                    ->orWhere('supplies.status', 'like', "%{$search}%")
                    ->orWhere('supplies.payment_type', 'like', "%{$search}%");
            });
        }

        return $q;
    }
}
