<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Supply extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'amount',
        'payment_type',
        'payment_date',
        'payment_month',
        'status',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status', '!=', 'cancelado');
    }

    public function scopeInPeriod(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('payment_date', [$from, $to]);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (! $this->receipt_path) {
            return null;
        }

        return Storage::disk('public')->url($this->receipt_path);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
