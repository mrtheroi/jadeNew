<?php

namespace App\Domain;

enum BusinessUnit: string
{
    case Jade = 'Jade';
    case JadeOrganico = 'Jade Orgánico';
    case KIN = 'KIN';

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Jade => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300',
            self::JadeOrganico => 'bg-lime-50 text-lime-700 ring-lime-600/20 dark:bg-lime-900/30 dark:text-lime-300',
            self::KIN => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-900/30 dark:text-indigo-300',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
