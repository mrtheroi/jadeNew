@props(['text', 'position' => 'top'])

@php
$positionClasses = match($position) {
    'top'    => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
    'left'   => 'right-full top-1/2 -translate-y-1/2 mr-2',
    'right'  => 'left-full top-1/2 -translate-y-1/2 ml-2',
};
@endphp

<div {{ $attributes->merge(['class' => 'group relative inline-flex']) }}>
    {{ $slot }}

    <span class="pointer-events-none absolute {{ $positionClasses }} whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-[11px] text-white opacity-0 shadow-sm transition-opacity group-hover:opacity-100 dark:bg-black">
        {{ $text }}
    </span>
</div>
