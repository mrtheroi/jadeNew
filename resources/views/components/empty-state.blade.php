@props(['title', 'description' => null])

<div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    @if(isset($icon))
        <div class="mb-4 text-gray-400 dark:text-gray-500">
            {{ $icon }}
        </div>
    @endif

    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>

    @if($description)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
    @endif

    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
