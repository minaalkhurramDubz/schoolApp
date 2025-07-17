<x-filament::page>
    <div class="text-left">
        <h2 class="text-xl font-extrabold mb-6 text-gray-800">Choose your school</h2>

        <div class="flex flex-col items-start space-y-4">
            @foreach ($schools as $school)
                <button
                    wire:click="setSchool({{ $school->id }})"
                    class="px-6 py-3 bg-amber-600 text-white text-lg font-semibold rounded-lg shadow hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                >
                    {{ $school->name }}
                </button>
            @endforeach
        </div>
    </div>
</x-filament::page>
