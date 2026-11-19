<x-layouts.app>
    <x-list-title>
        {{ $select }} Videos
    </x-list-title>

    <x-content-list
        :items="$feed"
        :links="$links"
        :count="$count"
    />
</x-layouts.app>
