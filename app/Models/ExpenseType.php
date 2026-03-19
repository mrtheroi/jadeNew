<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_type_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
