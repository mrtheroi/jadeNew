<?php

use App\Livewire\Expenses\PurchaseOrdersController;
use App\Livewire\Expenses\SuppliesController;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\Supply;
use App\Models\User;
use App\Services\PurchaseOrderGenerator;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->generator = app(PurchaseOrderGenerator::class);

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
});

// ─── SERVICE: generación de OC ──────────────────────────────────────────

test('generator creates OC with all eligible supplies of the day', function () {
    $today = Carbon::today();

    Supply::factory()->count(3)->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 1000,
        'payment_date' => $today,
        'purchase_order_id' => null,
    ]);

    $oc = $this->generator->generate('Jade', $today, $this->user->id);

    expect($oc->oc_number)->toStartWith('OC-Jade-');
    expect($oc->total_items)->toBe(3);
    expect((float) $oc->total_amount)->toBe(3000.0);
    expect($oc->isClosed())->toBeTrue();
    expect($oc->business_unit)->toBe('Jade');
});

test('generator throws when no supplies are eligible', function () {
    $today = Carbon::today();

    $this->generator->generate('Jade', $today, $this->user->id);
})->throws(RuntimeException::class);

test('generator only takes supplies of the same unit and day', function () {
    $today = Carbon::today();
    $yesterday = Carbon::yesterday();

    Supply::factory()->count(2)->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 500,
        'payment_date' => $today,
    ]);
    Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 999,
        'payment_date' => $yesterday,
    ]);
    Supply::factory()->create([
        'category_id' => $this->categoryKin->id,
        'amount' => 700,
        'payment_date' => $today,
    ]);

    $oc = $this->generator->generate('Jade', $today, $this->user->id);

    expect($oc->total_items)->toBe(2);
    expect((float) $oc->total_amount)->toBe(1000.0);
});

test('generator skips supplies already assigned to an OC', function () {
    $today = Carbon::today();

    $existingOc = PurchaseOrder::factory()->forUnit('Jade')->create();

    Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 500,
        'payment_date' => $today,
        'purchase_order_id' => $existingOc->id,
    ]);
    Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 800,
        'payment_date' => $today,
        'purchase_order_id' => null,
    ]);

    $oc = $this->generator->generate('Jade', $today, $this->user->id);

    expect($oc->total_items)->toBe(1);
    expect((float) $oc->total_amount)->toBe(800.0);
});

test('generator regenerates with suffix when same day-unit already has an OC', function () {
    $today = Carbon::today();

    Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 500,
        'payment_date' => $today,
    ]);
    $first = $this->generator->generate('Jade', $today, $this->user->id);

    Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 300,
        'payment_date' => $today,
    ]);
    $second = $this->generator->generate('Jade', $today, $this->user->id);

    expect($first->oc_number)->not->toBe($second->oc_number);
    expect($second->oc_number)->toEndWith('-2');
});

// ─── INMUTABILIDAD: bloqueo de Supply con OC cerrada ────────────────────

test('isLocked returns true for supplies with closed OC', function () {
    $oc = PurchaseOrder::factory()->create();
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => $oc->id,
    ]);

    expect($supply->fresh()->isLocked())->toBeTrue();
});

test('isLocked returns false for supplies without OC', function () {
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => null,
    ]);

    expect($supply->isLocked())->toBeFalse();
});

test('isLocked returns false for supplies with cancelled OC', function () {
    $oc = PurchaseOrder::factory()->cancelled()->create();
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => $oc->id,
    ]);

    expect($supply->fresh()->isLocked())->toBeFalse();
});

// ─── SERVICE: anulación libera supplies ─────────────────────────────────

test('cancelling an OC releases its supplies', function () {
    $today = Carbon::today();

    Supply::factory()->count(2)->create([
        'category_id' => $this->categoryJade->id,
        'payment_date' => $today,
    ]);

    $oc = $this->generator->generate('Jade', $today, $this->user->id);

    expect(Supply::where('purchase_order_id', $oc->id)->count())->toBe(2);

    $this->generator->cancel($oc);

    expect($oc->fresh()->isCancelled())->toBeTrue();
    expect(Supply::where('purchase_order_id', $oc->id)->count())->toBe(0);
});

// ─── LIVEWIRE Supplies: bloqueo y generación ────────────────────────────

test('SuppliesController blocks editing supplies with closed OC', function () {
    $oc = PurchaseOrder::factory()->create();
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => $oc->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('edit', $supply->id)
        ->assertSet('open', false);
});

test('SuppliesController blocks deleting supplies with closed OC', function () {
    $oc = PurchaseOrder::factory()->create();
    $supply = Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'purchase_order_id' => $oc->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('destroy', $supply->id);

    expect(Supply::find($supply->id))->not->toBeNull();
});

test('previewGeneratePurchaseOrder requires a business_unit', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('business_unit', '')
        ->call('previewGeneratePurchaseOrder')
        ->assertSet('showGenerateOcModal', false);
});

test('previewGeneratePurchaseOrder shows preview when there are eligible supplies', function () {
    $today = Carbon::today();
    Supply::factory()->count(2)->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 600,
        'payment_date' => $today,
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('business_unit', 'Jade')
        ->set('period_key', $today->format('Y-m'))
        ->call('previewGeneratePurchaseOrder')
        ->assertSet('showGenerateOcModal', true)
        ->assertSet('ocPreviewCount', 2)
        ->assertSet('ocPreviewTotal', 1200.0);
});

test('confirmGeneratePurchaseOrder creates the OC', function () {
    $today = Carbon::today();
    Supply::factory()->create([
        'category_id' => $this->categoryJade->id,
        'amount' => 1500,
        'payment_date' => $today,
    ]);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->set('business_unit', 'Jade')
        ->set('period_key', $today->format('Y-m'))
        ->call('previewGeneratePurchaseOrder')
        ->call('confirmGeneratePurchaseOrder')
        ->assertSet('showGenerateOcModal', false);

    expect(PurchaseOrder::where('business_unit', 'Jade')->count())->toBe(1);
});

// ─── LIVEWIRE Purchase Orders: filtros y anulación ──────────────────────

test('PurchaseOrdersController filters by business_unit', function () {
    PurchaseOrder::factory()->forUnit('Jade')->count(2)->create();
    PurchaseOrder::factory()->forUnit('KIN')->count(1)->create();

    Livewire::actingAs($this->user)
        ->test(PurchaseOrdersController::class)
        ->set('business_unit', 'Jade')
        ->assertViewHas('orders', fn ($orders) => $orders->total() === 2);
});

test('PurchaseOrdersController cancel anula la OC y libera supplies', function () {
    $today = Carbon::today();
    Supply::factory()->count(2)->create([
        'category_id' => $this->categoryJade->id,
        'payment_date' => $today,
    ]);
    $oc = $this->generator->generate('Jade', $today, $this->user->id);

    Livewire::actingAs($this->user)
        ->test(PurchaseOrdersController::class)
        ->call('cancel', $oc->id);

    expect($oc->fresh()->isCancelled())->toBeTrue();
    expect(Supply::where('purchase_order_id', $oc->id)->count())->toBe(0);
});
