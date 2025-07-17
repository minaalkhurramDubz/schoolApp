<x-filament::page>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    
    
    <x-filament::card>
            <div class="text-sm text-gray-500">Students</div>
            <div class="text-2xl font-bold">{{ $this->studentCount }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Teachers</div>
            <div class="text-2xl font-bold">{{ $this->teacherCount }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Courses</div>
            <div class="text-2xl font-bold">{{ $this->courseCount }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Classes</div>
            <div class="text-2xl font-bold">{{ $this->classCount }}</div>
        </x-filament::card>
    </div>
</x-filament::page>
