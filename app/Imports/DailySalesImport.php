<?php

namespace App\Imports;

use App\Models\DailySale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DailySalesImport implements ToArray, WithHeadingRow
{
    public function __construct(
        public string $businessUnit,
        public string $operationDate,
        public ?int $userId = null,
    ) {}

    public function array(array $rows): void
    {
        $totals = [
            'alimentos' => 0,
            'bebidas' => 0,
            'otros' => 0,
            'subtotal' => 0,
            'iva' => 0,
            'total' => 0,
        ];

        foreach ($rows as $row) {
            $totals['alimentos'] += $this->toFloat($row['alimentos'] ?? 0);
            $totals['bebidas'] += $this->toFloat($row['bebidas'] ?? 0);
            $totals['otros'] += $this->toFloat($row['otros'] ?? 0);
            $totals['subtotal'] += $this->toFloat($row['subtotal'] ?? 0);
            $totals['iva'] += $this->toFloat($row['iva'] ?? 0);
            $totals['total'] += $this->toFloat($row['total'] ?? 0);
        }

        $date = Carbon::parse($this->operationDate)->toDateString();

        $existing = DailySale::query()
            ->where('business_unit', $this->businessUnit)
            ->whereDate('operation_date', $date)
            ->first();

        if ($existing) {
            $existing->update([
                ...$totals,
                'user_id' => $this->userId,
            ]);
        } else {
            DailySale::create([
                'business_unit' => $this->businessUnit,
                'operation_date' => $date,
                ...$totals,
                'user_id' => $this->userId,
            ]);
        }
    }

    private function toFloat(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }
}
