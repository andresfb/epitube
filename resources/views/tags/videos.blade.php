<x-layouts.app>
    <x-list-title>
        Videos Tagged as: <span class="font-normal text-gray-600">{{ $tag->name }}</span>
    </x-list-title>

    <x-content-list
        :items="$feed"
        :links="$links"
        :count="$count"
    />
</x-layouts.app>
