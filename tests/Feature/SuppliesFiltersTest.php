<?php

use App\Application\Supplies\SuppliesQuery;
use App\Livewire\Expenses\SuppliesController;
use App\Models\Category;
use App\Models\ExpenseType;
use App\Models\Supply;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->query = app(SuppliesQuery::class);

    // Catálogo de tipos.
    $this->typeAlimentos = ExpenseType::factory()->create(['expense_type_name' => 'INSUMO ALIMENTOS']);
    $this->typeBebidas = ExpenseType::factory()->create(['expense_type_name' => 'INSUMO BEBIDAS']);
    $this->typeVariables = ExpenseType::factory()->create(['expense_type_name' => 'VARIABLES']);

    // Categorías Jade — distintos proveedores y tipos para cubrir cascada.
    $this->frutas = Category::factory()->create([
        'business_unit' => 'Jade',
        'expense_type_id' => $this->typeAlimentos->id,
        'expense_name' => 'FRUTAS Y VERDURAS',
        'provider_name' => 'CHEDRAUI',
    ]);
    $this->vinos = Category::factory()->create([
        'business_unit' => 'Jade',
        'expense_type_id' => $this->typeBebidas->id,
        'expense_name' => 'VINOS Y LICORES',
        'provider_name' => 'CHEDRAUI',
    ]);
    $this->limpieza = Category::factory()->create([
        'business_unit' => 'Jade',
        'expense_type_id' => $this->typeVariables->id,
        'expense_name' => 'LIMPIEZA',
        'provider_name' => 'WALMART',
    ]);

    // Categoría en otra unidad — para asegurar aislamiento por business_unit.
    $this->kinFood = Category::factory()->create([
        'business_unit' => 'KIN',
        'expense_type_id' => $this->typeAlimentos->id,
        'expense_name' => 'COMIDA KIN',
        'provider_name' => 'SORIANA',
    ]);

    // Supplies dentro del rango de abril 2026.
    Supply::factory()->create([
        'category_id' => $this->frutas->id,
        'amount' => 1000,
        'status' => 'pagado',
        'payment_date' => '2026-04-10',
        'payment_month' => '2026-04',
    ]);
    Supply::factory()->create([
        'category_id' => $this->vinos->id,
        'amount' => 500,
        'status' => 'pagado',
        'payment_date' => '2026-04-15',
        'payment_month' => '2026-04',
    ]);
    Supply::factory()->create([
        'category_id' => $this->limpieza->id,
        'amount' => 300,
        'status' => 'pagado',
        'payment_date' => '2026-04-20',
        'payment_month' => '2026-04',
    ]);

    // Cancelado dentro del rango — debe verse en la tabla pero no sumar al total.
    Supply::factory()->create([
        'category_id' => $this->frutas->id,
        'amount' => 999,
        'status' => 'cancelado',
        'payment_date' => '2026-04-12',
        'payment_month' => '2026-04',
    ]);

    // Supply de otra unidad — no debe interferir cuando filtramos por Jade.
    Supply::factory()->create([
        'category_id' => $this->kinFood->id,
        'amount' => 7000,
        'status' => 'pagado',
        'payment_date' => '2026-04-05',
        'payment_month' => '2026-04',
    ]);

    $this->jadeApril = [
        'search' => '',
        'business_unit' => 'Jade',
        'expense_type_id' => '',
        'category_id' => '',
        'date_from' => '2026-04-01',
        'date_to' => '2026-04-30',
    ];
});

// ─── BACKEND: filtros base ──────────────────────────────────────────────

test('base filters by expense_type_id', function () {
    $filters = ['expense_type_id' => $this->typeAlimentos->id] + $this->jadeApril;

    $supplies = $this->query->base($filters)->get();

    expect($supplies)->toHaveCount(2);
    expect($supplies->pluck('category_id')->unique()->values()->all())
        ->toEqual([$this->frutas->id]);
});

test('base filters by category_id', function () {
    $filters = ['category_id' => $this->vinos->id] + $this->jadeApril;

    $supplies = $this->query->base($filters)->get();

    expect($supplies)->toHaveCount(1);
    expect($supplies->first()->category_id)->toBe($this->vinos->id);
});

test('base includes cancelados (table shows them for visibility)', function () {
    $supplies = $this->query->base($this->jadeApril)->get();

    // 3 pagados + 1 cancelado = 4 (Jade abril, sin filtro de tipo/categoría).
    expect($supplies)->toHaveCount(4);
    expect($supplies->pluck('status')->unique()->sort()->values()->all())
        ->toEqual(['cancelado', 'pagado']);
});

// ─── BACKEND: totales y breakdown ───────────────────────────────────────

test('totalGeneral excludes cancelados', function () {
    // Pagados: 1000 + 500 + 300 = 1800. Cancelado 999 NO suma.
    expect($this->query->totalGeneral($this->jadeApril))->toBe(1800.0);
});

test('totalsByExpenseType excludes cancelados and groups by type', function () {
    $breakdown = $this->query->totalsByExpenseType($this->jadeApril);

    // 3 tipos representados: INSUMO ALIMENTOS (1000 — solo pagado), INSUMO BEBIDAS (500), VARIABLES (300).
    expect($breakdown)->toHaveCount(3);

    $byType = $breakdown->keyBy('expense_type_name');
    expect((float) $byType['INSUMO ALIMENTOS']->total_amount)->toBe(1000.0);
    expect((float) $byType['INSUMO BEBIDAS']->total_amount)->toBe(500.0);
    expect((float) $byType['VARIABLES']->total_amount)->toBe(300.0);
});

test('breakdown sum equals totalGeneral', function () {
    $total = $this->query->totalGeneral($this->jadeApril);
    $breakdown = $this->query->totalsByExpenseType($this->jadeApril);

    $sum = (float) $breakdown->sum('total_amount');

    expect($sum)->toBe($total);
});

test('cancelledTotal returns only sum of cancelados', function () {
    expect($this->query->cancelledTotal($this->jadeApril))->toBe(999.0);
});

test('cancelledCount returns only count of cancelados', function () {
    expect($this->query->cancelledCount($this->jadeApril))->toBe(1);
});

// ─── BACKEND: cascada de dropdowns ──────────────────────────────────────

test('expenseTypesForFilter cascades by search', function () {
    $filters = ['search' => 'CHEDRAUI'] + $this->jadeApril;

    $types = $this->query->expenseTypesForFilter($filters)
        ->pluck('expense_type_name')
        ->sort()
        ->values()
        ->all();

    // Chedraui solo tiene movimientos en INSUMO ALIMENTOS y INSUMO BEBIDAS (frutas y vinos).
    expect($types)->toEqual(['INSUMO ALIMENTOS', 'INSUMO BEBIDAS']);
});

test('categoriesForFilter cascades by search', function () {
    $filters = ['search' => 'CHEDRAUI'] + $this->jadeApril;

    $categories = $this->query->categoriesForFilter($filters);

    expect($categories->pluck('id')->sort()->values()->all())
        ->toEqual(collect([$this->frutas->id, $this->vinos->id])->sort()->values()->all());
});

test('categoriesForFilter cascades by expense_type_id', function () {
    $filters = ['expense_type_id' => $this->typeAlimentos->id] + $this->jadeApril;

    $categories = $this->query->categoriesForFilter($filters);

    // Solo frutas (Jade + INSUMO ALIMENTOS); kinFood queda fuera por business_unit.
    expect($categories->pluck('id')->all())->toEqual([$this->frutas->id]);
});

test('expenseTypesForFilter always includes selectedId even if no data matches', function () {
    $filters = ['search' => 'NO_EXISTE_NADA'] + $this->jadeApril;

    $types = $this->query->expenseTypesForFilter($filters, $this->typeVariables->id);

    expect($types->pluck('id')->all())->toContain($this->typeVariables->id);
});

test('categoriesForFilter always includes selectedId even if no data matches', function () {
    $filters = ['search' => 'NO_EXISTE_NADA'] + $this->jadeApril;

    $categories = $this->query->categoriesForFilter($filters, $this->limpieza->id);

    expect($categories->pluck('id')->all())->toContain($this->limpieza->id);
});

// ─── LIVEWIRE: cascada en el componente ─────────────────────────────────

test('changing expense_type_id clears selected category', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('business_unit', 'Jade')
        ->set('period_key', '2026-04')
        ->set('expense_type_id', (string) $this->typeBebidas->id)
        ->set('category_id', (string) $this->vinos->id)
        ->assertSet('category_id', (string) $this->vinos->id)
        // Cambiar tipo → categoría debe limpiarse.
        ->set('expense_type_id', (string) $this->typeAlimentos->id)
        ->assertSet('category_id', '');
});

test('changing business_unit clears expense_type_id and category_id', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('business_unit', 'Jade')
        ->set('period_key', '2026-04')
        ->set('expense_type_id', (string) $this->typeAlimentos->id)
        ->set('category_id', (string) $this->frutas->id)
        ->set('business_unit', 'KIN')
        ->assertSet('expense_type_id', '')
        ->assertSet('category_id', '');
});

test('resetFilters clears all filters', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('search', 'CHEDRAUI')
        ->set('business_unit', 'Jade')
        ->set('expense_type_id', (string) $this->typeAlimentos->id)
        ->set('category_id', (string) $this->frutas->id)
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('business_unit', '')
        ->assertSet('expense_type_id', '')
        ->assertSet('category_id', '')
        ->assertSet('period_key', now()->format('Y-m'));
});
