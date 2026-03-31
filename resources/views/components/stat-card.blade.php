@props(['label', 'value', 'subtitle' => null, 'variant' => 'default'])

@php
$valueClass = match($variant) {
    'success' => 'text-emerald-600 dark:text-emerald-400',
    'danger'  => 'text-rose-600 dark:text-rose-400',
    'warning' => 'text-amber-600 dark:text-amber-400',
    'info'    => 'text-blue-600 dark:text-blue-400',
    default   => 'text-gray-900 dark:text-white',
};
@endphp

<x-card class="p-4">
    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
    <p class="mt-2 text-xl font-semibold {{ $valueClass }}">{{ $value }}</p>
    @if($subtitle)
        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
    @endif
</x-card>
