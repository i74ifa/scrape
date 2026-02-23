<div class="flex items-center gap-4 p-4 border rounded-xl">
    <img src="{{ $getState()->image }}" class="w-16 h-16 rounded-lg object-cover">
    <div>
        <h4 class="font-bold">{{ $getState()->name }}</h4>
        <a href="{{ $getState()->link }}" target="_blank" class="text-blue-500 hover:underline">
            View Product
        </a>
    </div>
</div>