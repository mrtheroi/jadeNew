<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySale extends Model
{
    /** @use HasFactory<\Database\Factories\DailySaleFactory> */
    use HasFactory;

    protected $fillable = [
        'business_unit',
        'operation_date',
        'turno',
        'status',
        'error_message',
        'llama_job_id',
        'extraction_raw_json',
        'alimentos',
        'bebidas',
        'otros',
        'subtotal',
        'iva',
        'total',
        'efectivo_monto',
        'efectivo_propina',
        'debito_monto',
        'debito_propina',
        'credito_monto',
        'credito_propina',
        'credito_cliente_monto',
        'credito_cliente_propina',
        'numero_personas',
        'numero_cuentas',
        'promedio_por_persona',
        'cantidad_productos',
        'period_start',
        'period_end',
        'user_id',
        'reconciliation_status',
        'reconciliation_data',
        'reconciliation_notes',
        'reconciled_at',
        'reconciled_by',
    ];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',
            'turno' => 'integer',
            'extraction_raw_json' => 'array',
            'alimentos' => 'decimal:2',
            'bebidas' => 'decimal:2',
            'otros' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'iva' => 'decimal:2',
            'total' => 'decimal:2',
            'efectivo_monto' => 'decimal:2',
            'efectivo_propina' => 'decimal:2',
            'debito_monto' => 'decimal:2',
            'debito_propina' => 'decimal:2',
            'credito_monto' => 'decimal:2',
            'credito_propina' => 'decimal:2',
            'credito_cliente_monto' => 'decimal:2',
            'credito_cliente_propina' => 'decimal:2',
            'promedio_por_persona' => 'decimal:2',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'reconciliation_data' => 'array',
            'reconciled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function isReconciled(): bool
    {
        return $this->reconciliation_status === 'reconciled';
    }

    public function hasDiscrepancy(): bool
    {
        return $this->reconciliation_status === 'discrepancy';
    }

    public function isPendingReconciliation(): bool
    {
        return $this->isCompleted() && $this->reconciliation_status === null;
    }

    public function totalPropinas(): float
    {
        return (float) $this->efectivo_propina
            + (float) $this->debito_propina
            + (float) $this->credito_propina
            + (float) $this->credito_cliente_propina;
    }

    public function turnoLabel(): string
    {
        return match ($this->turno) {
            1 => 'Matutino',
            2 => 'Vespertino',
            default => 'Desconocido',
        };
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
}
