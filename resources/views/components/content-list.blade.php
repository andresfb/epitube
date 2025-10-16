@props(['items'])

<div class="grid grid-cols-1 gap-6 sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-4">
@foreach($items as $item)
    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
        {{-- Add your feed item content here --}}
        <div class="p-4">
            {{ $item->name ?? 'Item' }}
        </div>
    </div>
@endforeach
</div>
