<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_unit',
        'expense_type_id',
        'expense_name',
        'provider_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class);
    }
}
