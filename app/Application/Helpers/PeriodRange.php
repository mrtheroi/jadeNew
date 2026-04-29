<?php

namespace App\Application\Helpers;

use Carbon\Carbon;

class PeriodRange
{
    /**
     * Convierte un period_key (YYYY-MM) en rango de fechas [start, end, normalizedKey].
     *
     * El prefijo `!` en createFromFormat resetea los campos no especificados al epoch
     * (1970-01-01 00:00:00) en lugar de usar la fecha actual. Sin él, parsear "2026-02"
     * un dia 29+ del mes provoca overflow a marzo (Carbon usa el dia actual como dia
     * implicito, y 2026-02-29 no existe en un anio no bisiesto).
     *
     * @return array{0: string, 1: string, 2: string}
     */
    public static function fromKey(?string $periodKey): array
    {
        $pk = $periodKey;

        if (empty($pk) || ! preg_match('/^\d{4}-\d{2}$/', $pk)) {
            $pk = now()->format('Y-m');
        }

        $start = Carbon::createFromFormat('!Y-m', $pk)->startOfMonth()->toDateString();
        $end = Carbon::createFromFormat('!Y-m', $pk)->endOfMonth()->toDateString();

        return [$start, $end, $pk];
    }
}
