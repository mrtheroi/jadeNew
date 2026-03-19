# Supply Receipt Image Upload

## Summary

Add optional receipt image upload to supplies. Images are stored on S3 and viewable from the supplies table via a clickable icon that opens a modal with the full image.

## Decisions

- **Storage:** S3 disk (already configured)
- **Cardinality:** One image per supply
- **Required:** Optional (nullable)
- **Access:** Temporary signed URLs for security
- **Max size:** 5MB, image files only

## Database

New migration: add `receipt_path` (string, nullable) to `supplies` table.

```php
$table->string('receipt_path')->nullable()->after('notes');
```

This column stores the S3 object key (e.g., `receipts/45_1710856200.jpg`).

## Model: Supply

- Add `receipt_path` to `$fillable`
- Add accessor `receiptUrl` that returns a temporary signed URL via `Storage::disk('s3')->temporaryUrl()` or null if no receipt

## Livewire: SuppliesController

- Add `use WithFileUploads` trait
- New property: `$receipt` (temporary upload)
- New property: `$showReceiptModal = false`
- New property: `$receiptUrl = null` (for the modal)
- Validation rule: `'receipt' => 'nullable|image|max:5120'` (5MB, images only)

### save() changes

- If `$this->receipt` exists, store to S3 at `receipts/{id}_{timestamp}.{ext}`
- If editing and replacing image, delete the old file from S3 first
- Save the S3 key to `receipt_path`

### destroy() changes

- Before deleting the supply, delete the receipt from S3 if `receipt_path` is set

### New methods

- `showReceipt(int $id)` — load supply, generate temporary URL, open modal
- `closeReceipt()` — close the receipt modal

## Views

### Form Modal (form-supplies.blade.php)

New file input after "Observaciones":

- File input with `wire:model="receipt"` and `accept="image/*"`
- Loading indicator while uploading
- Preview of current image if editing a supply that already has a receipt
- Option to remove existing image

### Table (supplies-controller.blade.php)

- New image/photo icon in the actions column, next to the existing eye/edit/trash icons
- Only visible when `$supply->receipt_path` is not null
- `wire:click="showReceipt({{ $supply->id }})"`

### Receipt Modal (new partial: modals/receipt-view.blade.php)

- Simple modal with the image centered
- Image scales to fit viewport (max-w, max-h with object-contain)
- Close button

## Tests

1. Create supply **with** receipt image → verify `receipt_path` is stored and file exists on S3
2. Create supply **without** image → verify it works, `receipt_path` is null
3. Edit supply and **replace** image → old file deleted, new file stored
4. Delete supply with image → file removed from S3
