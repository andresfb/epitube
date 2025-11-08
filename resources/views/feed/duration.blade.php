<x-layouts.app>
    <div class="w-full text-2xl text-left font-semibold text-gray-700 mt-2 mb-8">
        {{ $duration }} Videos <span class="text-xs">{{ $range }}</span>
    </div>

    <x-content-list
        :items="$feed"
        :links="$links"
    />
</x-layouts.app>
