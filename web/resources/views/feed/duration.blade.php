<x-layouts.app>
    <x-list-title>
        {{ $duration }} Videos <span class="text-xs">{{ $range }}</span>
    </x-list-title>

    <x-content-list
        :items="$feed"
        :links="$links"
        :count="$count"
    />
</x-layouts.app>
