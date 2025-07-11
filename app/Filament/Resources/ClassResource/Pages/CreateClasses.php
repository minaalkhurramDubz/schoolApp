<?php

namespace App\Filament\Resources\ClassResource\Pages;

use App\Filament\Resources\ClassResource;
use App\Models\SchoolClass;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateClasses extends CreateRecord
{
    protected static string $resource = ClassResource::class;

    protected function handleRecordCreation(array $data): SchoolClass
    {
        $teachers = $data['teachers'] ?? [];
        unset($data['teachers']);

        // Generate slug if not already set
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $class = SchoolClass::create($data);

        $syncData = collect($teachers)->mapWithKeys(fn ($id) => [
            $id => ['role' => 'teacher'],
        ])->toArray();

        $class->teachers()->sync($syncData);

        return $class;
    }
}
