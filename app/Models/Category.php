<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
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

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

}
