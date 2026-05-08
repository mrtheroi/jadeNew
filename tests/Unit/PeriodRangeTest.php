<?php

use App\Application\Helpers\PeriodRange;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

describe('PeriodRange::fromKey', function () {
    it('returns correct range for a 31-day month', function () {
        Carbon::setTestNow(Carbon::parse('2026-04-15'));

        [$start, $end, $key] = PeriodRange::fromKey('2026-01');

        expect($start)->toBe('2026-01-01');
        expect($end)->toBe('2026-01-31');
        expect($key)->toBe('2026-01');
    });

    it('returns correct range for a 30-day month', function () {
        Carbon::setTestNow(Carbon::parse('2026-04-15'));

        [$start, $end] = PeriodRange::fromKey('2026-04');

        expect($start)->toBe('2026-04-01');
        expect($end)->toBe('2026-04-30');
    });

    it('returns February 1-28 for a non-leap year, even when today is day 29', function () {
        // Regression: cuando hoy es 29+, Carbon::createFromFormat('Y-m', '2026-02')
        // sin el prefijo `!` provocaba overflow a 2026-03-01 (porque 2026-02-29 no existe).
        // El filtro de febrero terminaba mostrando datos de marzo.
        Carbon::setTestNow(Carbon::parse('2026-04-29'));

        [$start, $end] = PeriodRange::fromKey('2026-02');

        expect($start)->toBe('2026-02-01');
        expect($end)->toBe('2026-02-28');
    });

    it('returns February 1-29 for a leap year', function () {
        Carbon::setTestNow(Carbon::parse('2024-04-30'));

        [$start, $end] = PeriodRange::fromKey('2024-02');

        expect($start)->toBe('2024-02-01');
        expect($end)->toBe('2024-02-29');
    });

    it('returns correct range for 30-day months when today is day 31', function () {
        // Regression: cuando hoy es 31, los meses con 30 dias (abr, jun, sep, nov)
        // overflow al mes siguiente sin el prefijo `!`.
        Carbon::setTestNow(Carbon::parse('2026-07-31'));

        foreach (['2026-04' => '2026-04-30', '2026-06' => '2026-06-30', '2026-09' => '2026-09-30', '2026-11' => '2026-11-30'] as $key => $expectedEnd) {
            [$start, $end] = PeriodRange::fromKey($key);

            expect($start)->toBe(substr($expectedEnd, 0, 8).'01');
            expect($end)->toBe($expectedEnd);
        }
    });

    it('falls back to current month when key is empty', function () {
        Carbon::setTestNow(Carbon::parse('2026-04-29'));

        [$start, $end, $key] = PeriodRange::fromKey(null);

        expect($key)->toBe('2026-04');
        expect($start)->toBe('2026-04-01');
        expect($end)->toBe('2026-04-30');
    });

    it('falls back to current month when key has invalid format', function () {
        Carbon::setTestNow(Carbon::parse('2026-04-29'));

        [$start, $end, $key] = PeriodRange::fromKey('not-a-date');

        expect($key)->toBe('2026-04');
        expect($start)->toBe('2026-04-01');
        expect($end)->toBe('2026-04-30');
    });
});
