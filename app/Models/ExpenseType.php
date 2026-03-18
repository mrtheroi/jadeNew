<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    protected $fillable = [
        'expense_type_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
