<x-layouts.app>
    <div class="container mx-auto px-4 py-8">
        <x-content-list :items="$feed" :timeout="$timeout" :maxRefresh="$maxRefresh" />
    </div>
</x-layouts.app>
