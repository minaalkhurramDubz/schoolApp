<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function afterSave(): void
    {
        $teachers = $this->data['teachers'] ?? [];

        // Sync teachers with role 'teacher'
        $syncData = collect($teachers)->mapWithKeys(function ($id) {
            return [$id => ['role' => 'teacher']];
        })->toArray();

        $this->record->teachers()->sync($syncData);
    }
}
