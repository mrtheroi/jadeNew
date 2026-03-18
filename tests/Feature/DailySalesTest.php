<?php

use App\Jobs\ProcessLlamaExtractionJob;
use App\Livewire\DailySalesController;
use App\Models\DailySale;
use App\Models\User;
use App\Services\DailySaleExtractionMapper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('guests are redirected from ventas page', function () {
    $this->get(route('ventas'))->assertRedirect(route('login'));
});

test('authenticated users can visit ventas page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('ventas'))->assertSuccessful();
});

test('ventas page shows daily sales records', function () {
    $user = User::factory()->create();

    DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => now()->format('Y-m-d'),
        'turno' => 1,
        'total' => 5000.00,
    ]);

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->assertSee('5,000.00');
});

test('ventas page filters by business unit', function () {
    $user = User::factory()->create();

    DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => now()->format('Y-m-d'),
        'turno' => 1,
        'total' => 1000.00,
    ]);

    DailySale::factory()->create([
        'business_unit' => 'KIN',
        'operation_date' => now()->format('Y-m-d'),
        'turno' => 1,
        'total' => 2000.00,
    ]);

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->set('filterBusinessUnit', 'Jade')
        ->assertSee('1,000.00')
        ->assertDontSee('2,000.00');
});

test('ventas page filters by period', function () {
    $user = User::factory()->create();

    DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => '2026-03-15',
        'turno' => 1,
        'total' => 3000.00,
    ]);

    DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => '2026-02-15',
        'turno' => 1,
        'total' => 4000.00,
    ]);

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->set('period_key', '2026-03')
        ->assertSee('3,000.00')
        ->assertDontSee('4,000.00');
});

test('upload pdf creates daily sale with processing status and dispatches job', function () {
    Queue::fake();
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('reporte.pdf', 1024, 'application/pdf');

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->set('business_unit', 'Jade')
        ->set('operation_date', now()->format('Y-m-d'))
        ->set('turno', 1)
        ->set('file', $file)
        ->call('uploadPdf');

    $sale = DailySale::where('business_unit', 'Jade')
        ->where('turno', 1)
        ->first();

    expect($sale)->not->toBeNull()
        ->and($sale->status)->toBe('processing')
        ->and($sale->user_id)->toBe($user->id);

    Queue::assertPushed(ProcessLlamaExtractionJob::class);
});

test('upload pdf rejects duplicate completed record', function () {
    Queue::fake();
    $user = User::factory()->create();

    DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => now()->format('Y-m-d'),
        'turno' => 1,
        'status' => 'completed',
    ]);

    $file = UploadedFile::fake()->create('reporte.pdf', 1024, 'application/pdf');

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->set('business_unit', 'Jade')
        ->set('operation_date', now()->format('Y-m-d'))
        ->set('turno', 1)
        ->set('file', $file)
        ->call('uploadPdf')
        ->assertHasErrors('file');

    Queue::assertNotPushed(ProcessLlamaExtractionJob::class);
});

test('upload pdf allows retry for failed record', function () {
    Queue::fake();
    $user = User::factory()->create();

    $failed = DailySale::factory()->failed()->create([
        'business_unit' => 'Jade',
        'operation_date' => now()->format('Y-m-d'),
        'turno' => 1,
    ]);

    $file = UploadedFile::fake()->create('reporte.pdf', 1024, 'application/pdf');

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->set('business_unit', 'Jade')
        ->set('operation_date', now()->format('Y-m-d'))
        ->set('turno', 1)
        ->set('file', $file)
        ->call('uploadPdf');

    expect(DailySale::find($failed->id))->toBeNull();
    expect(DailySale::where('business_unit', 'Jade')->where('turno', 1)->where('status', 'processing')->count())->toBe(1);

    Queue::assertPushed(ProcessLlamaExtractionJob::class);
});

test('destroy rejects completed records', function () {
    $user = User::factory()->create();

    $sale = DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => now()->format('Y-m-d'),
        'turno' => 1,
        'status' => 'completed',
    ]);

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->call('destroy', $sale->id);

    expect(DailySale::find($sale->id))->not->toBeNull();
});

test('destroy allows deleting failed records', function () {
    $user = User::factory()->create();

    $sale = DailySale::factory()->failed()->create([
        'business_unit' => 'Jade',
        'operation_date' => now()->format('Y-m-d'),
        'turno' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->call('destroy', $sale->id);

    expect(DailySale::find($sale->id))->toBeNull();
});

test('daily sales detail modal shows data', function () {
    $user = User::factory()->create();

    $sale = DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => now()->format('Y-m-d'),
        'turno' => 1,
        'total' => 7500.00,
    ]);

    Livewire::actingAs($user)
        ->test(DailySalesController::class)
        ->call('showDetail', $sale->id)
        ->assertSet('showDetailModal', true)
        ->assertSet('detailSale.id', $sale->id);
});

test('webhook extract.success updates daily sale to completed', function () {
    $sale = DailySale::factory()->processing()->create([
        'llama_job_id' => 'job_test123',
    ]);

    $mockResult = [
        'sales_by_area' => [
            ['area_name' => 'COMEDOR', 'food_sales' => 29566.62, 'beverage_sales' => 14922.77, 'other_sales' => 5998.14, 'subtotal' => 50487.55, 'tax' => 4039, 'total' => 54526.56, 'number_of_people' => 225, 'number_of_accounts' => 606, 'average_per_person' => 224.38, 'product_count' => 127],
        ],
        'payment_summary' => [
            ['payment_method' => 'EFECTIVO', 'amount' => 14795, 'tip' => 1195],
            ['payment_method' => 'TARJETA DEBITO', 'amount' => 15189, 'tip' => 1176.8],
            ['payment_method' => 'TARJETA CREDITO', 'amount' => 20151, 'tip' => 2038.1],
            ['payment_method' => 'CREDITO', 'amount' => 4391.56, 'tip' => 0],
        ],
        'report_period' => [
            'start_datetime' => '17/03/2026 07:00:00 AM',
            'end_datetime' => '17/03/2026 11:59:59 PM',
        ],
    ];

    $this->mock(\App\Services\LlamaIndexService::class, function ($mock) use ($mockResult) {
        $mock->shouldReceive('getExtractJob')
            ->with('job_test123')
            ->once()
            ->andReturn(new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode($mockResult))
            ));
    });

    $response = $this->postJson('/api/webhook/llama', [
        'event_type' => 'extract.success',
        'event_id' => 'evt_test_1',
        'data' => ['job_id' => 'job_test123'],
    ]);

    $response->assertOk();

    $sale->refresh();
    expect($sale->status)->toBe('completed')
        ->and((float) $sale->alimentos)->toBe(29566.62)
        ->and((float) $sale->total)->toBe(54526.56)
        ->and((float) $sale->efectivo_monto)->toBe(14795.0)
        ->and($sale->numero_personas)->toBe(225)
        ->and($sale->extraction_raw_json)->not->toBeNull();
});

test('webhook extract.error marks daily sale as failed', function () {
    $sale = DailySale::factory()->processing()->create([
        'llama_job_id' => 'job_fail123',
    ]);

    $response = $this->postJson('/api/webhook/llama', [
        'event_type' => 'extract.error',
        'event_id' => 'evt_fail_1',
        'data' => ['job_id' => 'job_fail123'],
    ]);

    $response->assertOk();

    $sale->refresh();
    expect($sale->status)->toBe('failed')
        ->and($sale->error_message)->not->toBeNull();
});

test('webhook with unknown job_id returns 404', function () {
    $response = $this->postJson('/api/webhook/llama', [
        'event_type' => 'extract.success',
        'event_id' => 'evt_unknown',
        'data' => ['job_id' => 'nonexistent_job'],
    ]);

    $response->assertNotFound();
});

test('webhook without job_id returns 400', function () {
    $response = $this->postJson('/api/webhook/llama', [
        'event_type' => 'extract.success',
        'event_id' => 'evt_no_job',
        'data' => [],
    ]);

    $response->assertStatus(400);
});

test('extraction mapper parses json correctly', function () {
    $mapper = new DailySaleExtractionMapper;

    $data = [
        'sales_by_area' => [
            ['area_name' => 'PLANTA ALTA', 'food_sales' => 0, 'total' => 0],
            ['area_name' => 'COMEDOR', 'food_sales' => 29566.62, 'beverage_sales' => 14922.77, 'other_sales' => 5998.14, 'subtotal' => 50487.55, 'tax' => 4039, 'total' => 54526.56, 'number_of_people' => 225, 'number_of_accounts' => 606, 'average_per_person' => 224.38, 'product_count' => 127],
        ],
        'payment_summary' => [
            ['payment_method' => 'EFECTIVO', 'amount' => 14795, 'tip' => 1195],
            ['payment_method' => 'TARJETA DEBITO', 'amount' => 15189, 'tip' => 1176.8],
        ],
        'report_period' => [
            'start_datetime' => '17/03/2026 07:00:00 AM',
            'end_datetime' => '17/03/2026 03:00:00 PM',
        ],
    ];

    $result = $mapper->map($data);

    expect($result['alimentos'])->toBe(29566.62)
        ->and($result['total'])->toBe(54526.56)
        ->and($result['numero_personas'])->toBe(225)
        ->and($result['efectivo_monto'])->toBe(14795.0)
        ->and($result['debito_monto'])->toBe(15189.0)
        ->and($result['period_start'])->not->toBeNull();
});

test('unique constraint allows same date different turno', function () {
    DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => '2026-03-18',
        'turno' => 1,
    ]);

    $sale2 = DailySale::factory()->create([
        'business_unit' => 'Jade',
        'operation_date' => '2026-03-18',
        'turno' => 2,
    ]);

    expect($sale2)->not->toBeNull();
    expect(DailySale::where('business_unit', 'Jade')->whereDate('operation_date', '2026-03-18')->count())->toBe(2);
});
