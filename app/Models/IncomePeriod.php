<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomePeriod extends Model
{
    protected $fillable = [
        'business_unit',
        'period_key',
        'income_amount',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'income_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
