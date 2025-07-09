<x-filament::page>
    <h2 class="text-lg font-bold mb-4">Choose your school</h2>

    <ul class="space-y-2">
        @foreach ($schools as $school)
            <li>
                <a 
                    href="{{ route('choose.role', ['school' => $school->id]) }}"
                    class="px-3 py-2 bg-amber-500 text-white rounded inline-block"
                >
                    {{ $school->name }}
                </a>
            </li>
        @endforeach
    </ul>
</x-filament::page>
