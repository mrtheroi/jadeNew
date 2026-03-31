<?php

use App\Livewire\SuppliesController;
use App\Models\Category;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create(['business_unit' => 'Jade']);
});

test('supply can be created with receipt image', function () {
    $image = UploadedFile::fake()->image('receipt.jpg', 400, 300);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('create')
        ->set('form.category_id', $this->category->id)
        ->set('form.amount', 1500)
        ->set('form.status', 'pendiente')
        ->set('form.payment_date', now()->format('Y-m-d'))
        ->set('form.receipt', $image)
        ->call('save')
        ->assertHasNoErrors();

    $supply = Supply::first();

    expect($supply)->not->toBeNull()
        ->and($supply->receipt_path)->not->toBeNull()
        ->and($supply->receipt_path)->toStartWith('receipts/');

    Storage::disk('public')->assertExists($supply->receipt_path);
});

test('supply can be created without receipt image', function () {
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('create')
        ->set('form.category_id', $this->category->id)
        ->set('form.amount', 800)
        ->set('form.status', 'pagado')
        ->set('form.payment_date', now()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors();

    $supply = Supply::first();

    expect($supply)->not->toBeNull()
        ->and($supply->receipt_path)->toBeNull();
});

test('supply receipt can be replaced on edit', function () {
    $originalImage = UploadedFile::fake()->image('original.jpg', 400, 300);

    // Create with original image
    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('create')
        ->set('form.category_id', $this->category->id)
        ->set('form.amount', 2000)
        ->set('form.status', 'pendiente')
        ->set('form.payment_date', now()->format('Y-m-d'))
        ->set('form.receipt', $originalImage)
        ->call('save');

    $supply = Supply::first();
    $oldPath = $supply->receipt_path;

    Storage::disk('public')->assertExists($oldPath);

    // Edit and replace with new image
    $newImage = UploadedFile::fake()->image('new_receipt.png', 600, 400);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('edit', $supply->id)
        ->set('form.receipt', $newImage)
        ->call('save')
        ->assertHasNoErrors();

    $supply->refresh();

    expect($supply->receipt_path)->not->toBe($oldPath);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($supply->receipt_path);
});

test('supply receipt is deleted when supply is destroyed', function () {
    $image = UploadedFile::fake()->image('receipt.jpg', 400, 300);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('create')
        ->set('form.category_id', $this->category->id)
        ->set('form.amount', 500)
        ->set('form.status', 'pendiente')
        ->set('form.payment_date', now()->format('Y-m-d'))
        ->set('form.receipt', $image)
        ->call('save');

    $supply = Supply::first();
    $receiptPath = $supply->receipt_path;

    Storage::disk('public')->assertExists($receiptPath);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('destroy', $supply->id);

    expect(Supply::find($supply->id))->toBeNull();

    Storage::disk('public')->assertMissing($receiptPath);
});

test('receipt validation rejects non-image files', function () {
    $file = UploadedFile::fake()->create('document.txt', 1024, 'text/plain');

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('create')
        ->set('form.category_id', $this->category->id)
        ->set('form.amount', 1000)
        ->set('form.status', 'pendiente')
        ->set('form.receipt', $file)
        ->call('save')
        ->assertHasErrors(['form.receipt']);
});

test('receipt validation rejects files over 5MB', function () {
    $image = UploadedFile::fake()->image('huge.jpg')->size(6000);

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('create')
        ->set('form.category_id', $this->category->id)
        ->set('form.amount', 1000)
        ->set('form.status', 'pendiente')
        ->set('form.receipt', $image)
        ->call('save')
        ->assertHasErrors(['form.receipt']);
});

test('show receipt modal displays for supply with image', function () {
    $supply = Supply::factory()->create([
        'category_id' => $this->category->id,
        'receipt_path' => 'receipts/test_image.jpg',
    ]);

    Storage::disk('public')->put('receipts/test_image.jpg', 'fake-image-content');

    Livewire::actingAs($this->user)
        ->test(SuppliesController::class)
        ->call('showReceipt', $supply->id)
        ->assertSet('showReceiptModal', true)
        ->assertNotSet('receiptUrl', null);
});
