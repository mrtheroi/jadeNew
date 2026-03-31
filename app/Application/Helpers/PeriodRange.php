<?php

namespace App\Application\Helpers;

use Carbon\Carbon;

class PeriodRange
{
    /**
     * Convierte un period_key (YYYY-MM) en rango de fechas [start, end, normalizedKey].
     *
     * @return array{0: string, 1: string, 2: string}
     */
    public static function fromKey(?string $periodKey): array
    {
        $pk = $periodKey;

        if (empty($pk) || ! preg_match('/^\d{4}-\d{2}$/', $pk)) {
            $pk = now()->format('Y-m');
        }

        $start = Carbon::createFromFormat('Y-m', $pk)->startOfMonth()->toDateString();
        $end = Carbon::createFromFormat('Y-m', $pk)->endOfMonth()->toDateString();

        return [$start, $end, $pk];
    }
}
