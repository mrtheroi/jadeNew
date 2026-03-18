@props([
    'href',
    'active' => false,

    // tokens: indigo | emerald | amber | rose | cyan | violet | slate
    'tone' => 'indigo',
])

@php
    $tones = [
        'indigo' => 'text-indigo-600 dark:text-indigo-300',
        'emerald' => 'text-emerald-600 dark:text-emerald-300',
        'amber' => 'text-amber-600 dark:text-amber-300',
        'rose' => 'text-rose-600 dark:text-rose-300',
        'cyan' => 'text-cyan-600 dark:text-cyan-300',
        'violet' => 'text-violet-600 dark:text-violet-300',
        'slate' => 'text-slate-700 dark:text-slate-200',
    ];

    $activeIcon = $tones[$tone] ?? $tones['indigo'];

    $iconClass = $active
        ? $activeIcon
        : 'text-gray-400 dark:text-white/30';
@endphp

<flux:navlist.item :href="$href" wire:navigate>
    <div class="flex items-center gap-2">
        @if(isset($icon))
            <span class="inline-flex h-7 w-7 items-center justify-center {{ $iconClass }}">
                {{ $icon }}
            </span>
        @endif

        <span class="text-sm text-gray-700 dark:text-gray-200">
            {{ $slot }}
        </span>
    </div>
</flux:navlist.item>
