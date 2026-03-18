<?php

namespace App\Domain;

enum BusinessUnit: string
{
    case Jade = 'Jade';
    case FuegoAmbar = 'Fuego Ambar';
    case KIN = 'KIN';

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }
}
