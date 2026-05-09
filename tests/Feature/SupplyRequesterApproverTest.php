<?php

use App\Livewire\Expenses\PurchaseOrdersController;
use App\Livewire\Expenses\SuppliesController;
use App\Models\Category;
use App\Models\Employee;
use App\Models\PurchaseOrder;
use App\Models\Supply;
use App\Models\User;
use App\Services\PurchaseOrderGenerator;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->categoryJade = Category::factory()->create([
        'business_unit' => 'Jade',
        'expense_name' => 'FRUTAS',
        'provider_name' => 'CHEDRAUI',
    ]);
    $this->categoryKin = Category::factory()->create([
        'business_unit' => 'KIN',
        'expense_name' => 'CARNES',
        'provider_name' => 'COSTCO',
    ]);
    $this->jadeEmp1 = Employee::factory()->create(['business_unit' => 'Jade', 'is_active' => true, 'full_name' => 'María Jade']);
    $this->jadeEmp2 = Employee::factory()->create(['business_unit' => 'Jade', 'is_active' => true, 'full_name' => 'Pedro Jade']);
    $this->kinEmp = Employee::factory()->create(['business_unit' => 'KIN', 'is_active' => true, 'full_name' => 'Ana KIN']);
});

// ─── VALIDATION ─────────────────────────────────────────────────────────

test('SupplyForm requires requester_id when saving', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('form.business_unit', 'Jade')
        ->set('form.category_id', (string) $this->categoryJade->id)
        ->set('form.approver_id', $this->jadeEmp2->id)
        ->set('form.amount', '100')
        ->set('form.payment_date', Carbon::today()->toDateString())
        ->set('form.status', 'pendiente')
        ->call('save')
        ->assertHasErrors(['form.requester_id' => 'required']);

    expect(Supply::count())->toBe(0);
});

test('SupplyForm requires approver_id when saving', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('form.business_unit', 'Jade')
        ->set('form.category_id', (string) $this->categoryJade->id)
        ->set('form.requester_id', $this->jadeEmp1->id)
        ->set('form.amount', '100')
        ->set('form.payment_date', Carbon::today()->toDateString())
        ->set('form.status', 'pendiente')
        ->call('save')
        ->assertHasErrors(['form.approver_id' => 'required']);

    expect(Supply::count())->toBe(0);
});

test('save rejects requester from different business unit', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('form.business_unit', 'Jade')
        ->set('form.category_id', (string) $this->categoryJade->id)
        ->set('form.requester_id', $this->kinEmp->id)
        ->set('form.approver_id', $this->jadeEmp2->id)
        ->set('form.amount', '100')
        ->set('form.payment_date', Carbon::today()->toDateString())
        ->set('form.status', 'pendiente')
        ->call('save')
        ->assertHasErrors('form.requester_id');

    expect(Supply::count())->toBe(0);
});

test('save rejects approver from different business unit', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('form.business_unit', 'Jade')
        ->set('form.category_id', (string) $this->categoryJade->id)
        ->set('form.requester_id', $this->jadeEmp1->id)
        ->set('form.approver_id', $this->kinEmp->id)
        ->set('form.amount', '100')
        ->set('form.payment_date', Carbon::today()->toDateString())
        ->set('form.status', 'pendiente')
        ->call('save')
        ->assertHasErrors('form.approver_id');

    expect(Supply::count())->toBe(0);
});

test('save persists requester_id and approver_id when valid', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('form.business_unit', 'Jade')
        ->set('form.category_id', (string) $this->categoryJade->id)
        ->set('form.requester_id', $this->jadeEmp1->id)
        ->set('form.approver_id', $this->jadeEmp2->id)
        ->set('form.amount', '100')
        ->set('form.payment_date', Carbon::today()->toDateString())
        ->set('form.status', 'pendiente')
        ->call('save')
        ->assertHasNoErrors();

    $supply = Supply::first();

    expect($supply->requester_id)->toBe($this->jadeEmp1->id);
    expect($supply->approver_id)->toBe($this->jadeEmp2->id);
});

// ─── BACKFILL MODE (bypass parcial del lock) ─────────────────────────────

test('editing a supply with closed OC opens form in backfill mode', function () {
    $oc = PurchaseOrder::factory()->create();
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => $oc->id,
        'requester_id' => null,
        'approver_id' => null,
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('edit', $supply->id)
        ->assertSet('open', true)
        ->assertSet('backfillMode', true);
});

test('backfill save persists only requester and approver, leaving the rest intact', function () {
    $oc = PurchaseOrder::factory()->create();
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => $oc->id,
        'amount' => 5000,
        'status' => 'pagado',
        'requester_id' => null,
        'approver_id' => null,
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('edit', $supply->id)
        ->set('form.requester_id', $this->jadeEmp1->id)
        ->set('form.approver_id', $this->jadeEmp2->id)
        ->set('form.amount', '999')
        ->set('form.status', 'cancelado')
        ->call('save')
        ->assertHasNoErrors();

    $supply->refresh();

    expect($supply->requester_id)->toBe($this->jadeEmp1->id);
    expect($supply->approver_id)->toBe($this->jadeEmp2->id);
    expect((float) $supply->amount)->toBe(5000.0);
    expect($supply->status)->toBe('pagado');
});

test('backfill mode rejects employees from different business unit', function () {
    $oc = PurchaseOrder::factory()->create();
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => $oc->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('edit', $supply->id)
        ->set('form.requester_id', $this->kinEmp->id)
        ->set('form.approver_id', $this->jadeEmp2->id)
        ->call('save')
        ->assertHasErrors('form.requester_id');

    expect($supply->fresh()->requester_id)->toBeNull();
});

test('editing a supply without closed OC keeps backfill mode disabled', function () {
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => null,
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('edit', $supply->id)
        ->assertSet('open', true)
        ->assertSet('backfillMode', false);
});

// ─── EAGER LOADING + RENDER ──────────────────────────────────────────────

test('purchase order detail loads supplies with requester and approver eager', function () {
    Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'requester_id' => $this->jadeEmp1->id,
        'approver_id' => $this->jadeEmp2->id,
        'payment_date' => Carbon::today(),
    ]);

    $oc = app(PurchaseOrderGenerator::class)
        ->generate('Jade', Carbon::today(), $this->user->id);

    Livewire::actingAs($this->user)
        ->test(PurchaseOrdersController::class)
        ->call('showDetail', $oc->id)
        ->assertSet('showDetailModal', true);

    $loadedSupply = $oc->fresh()->load(['supplies.requester', 'supplies.approver'])->supplies->first();
    expect($loadedSupply->requester->full_name)->toBe('María Jade');
    expect($loadedSupply->approver->full_name)->toBe('Pedro Jade');
});

test('employeesForUnit stays empty when chosen unit has no active employees', function () {
    // Solo cargamos KIN; al elegir Jade Orgánico no debe haber empleados disponibles.
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('create')
        ->set('form.business_unit', 'Jade Orgánico')
        ->assertSet('employeesForUnit', []);
});

test('inactive employees are excluded from the employee dropdown', function () {
    Employee::factory()->create([
        'business_unit' => 'Jade',
        'is_active' => false,
        'full_name' => 'Inactivo Jade',
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('create')
        ->set('form.business_unit', 'Jade')
        ->assertCount('employeesForUnit', 2); // jadeEmp1 y jadeEmp2 — el inactivo NO entra
});

test('purchase order pdf endpoint responds successfully with requester/approver data', function () {
    Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'requester_id' => $this->jadeEmp1->id,
        'approver_id' => $this->jadeEmp2->id,
        'payment_date' => Carbon::today(),
    ]);

    $oc = app(PurchaseOrderGenerator::class)
        ->generate('Jade', Carbon::today(), $this->user->id);

    $response = $this->actingAs($this->user)->get(route('ordenes-compra.pdf', $oc->id));

    $response->assertSuccessful();
});
