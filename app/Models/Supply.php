<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{

    protected $fillable = [
        'category_id',
        'amount',
        'payment_type',
        'payment_date',
        'payment_month',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
