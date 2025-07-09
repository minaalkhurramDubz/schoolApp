<x-filament::page>
    <h2 class="text-lg font-bold mb-4">
        Choose your role
    </h2>

    <ul class="space-y-2">
        @foreach ($roles as $role)
            <li>
                <form method="POST" action="{{ route('select.school') }}">
                    @csrf
                    <input type="hidden" name="school_id" value="{{ $schoolId }}">
                    <input type="hidden" name="role" value="{{ $role }}">

                    <button class="px-3 py-2 bg-amber-500 text-white rounded">
                        {{ ucfirst($role) }}
                    </button>
                </form>
            </li>
        @endforeach
    </ul>
</x-filament::page>
