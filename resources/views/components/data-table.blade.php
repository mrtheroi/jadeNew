@props(['title' => null, 'count' => null, 'loadingTarget' => null])

<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
    {{-- Title bar --}}
    @if($title)
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                @if(! is_null($count))
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        {{ $count }}
                    </span>
                @endif
            </div>
        </div>
    @endif

    {{-- Table container --}}
    <div class="relative">
        {{-- Loading overlay --}}
        @if($loadingTarget)
            <div
                wire:loading
                wire:target="{{ $loadingTarget }}"
                class="absolute inset-0 z-10 flex items-center justify-center rounded-b-xl bg-white/70 dark:bg-gray-900/70"
            >
                <svg class="size-6 animate-spin text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </div>
        @endif

        <div class="max-h-[70vh] overflow-x-auto overflow-y-auto">
            <table class="w-full text-xs">
                @if(isset($header))
                    <thead class="sticky top-0 z-[5] border-b border-gray-200 bg-gray-50/95 backdrop-blur dark:border-white/10 dark:bg-gray-800/95">
                        {{ $header }}
                    </thead>
                @endif

                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer / Pagination --}}
    @if(isset($footer))
        <div class="border-t border-gray-200 px-4 py-3 dark:border-white/10">
            {{ $footer }}
        </div>
    @endif
</div>
