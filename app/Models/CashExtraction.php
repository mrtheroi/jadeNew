<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashExtraction extends Model
{
    protected $table = 'cash_extractions';

    protected $fillable = [
        'user_id',
        'turno',
        'operation_date',
        'image_path',
        'image_original_name',
        'cash_sales',
        'debit_card_sales',
        'credit_card_sales',
        'credit_sales',
        'total_sales_payment_methods',
        'cash_tips',
        'debit_card_tips',
        'credit_card_tips',
        'total_tips_payment_methods',
        'monto_debito',
        'monto_credito',
        'efectivo',
        'run_id',
        'extraction_agent_id',
        'extraction_metadata',
        'status',
        'error_message',
        'business_unit',   // 👈 nuevo campo
        'cash_validation_result',
        'cash_validation_note',
    ];

    protected $casts = [
        'operation_date' => 'date',
        'extraction_metadata' => 'array',
        'cash_sales' => 'decimal:2',
        'debit_card_sales' => 'decimal:2',
        'credit_card_sales' => 'decimal:2',
        'credit_sales' => 'decimal:2',
        'total_sales_payment_methods' => 'decimal:2',
        'cash_tips' => 'decimal:2',
        'debit_card_tips' => 'decimal:2',
        'credit_card_tips' => 'decimal:2',
        'total_tips_payment_methods' => 'decimal:2',
        'monto_debito' => 'decimal:2',
        'monto_credito' => 'decimal:2',
        'efectivo' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
