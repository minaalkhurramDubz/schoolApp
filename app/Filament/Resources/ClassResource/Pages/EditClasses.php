<?php

namespace App\Filament\Resources\ClassResource\Pages;

use App\Filament\Resources\ClassResource;
use Filament\Resources\Pages\EditRecord;

class EditClasses extends EditRecord
{
    protected static string $resource = ClassResource::class;

    protected function afterSave(): void
    {
        $teachers = $this->data['teachers'] ?? [];

        $syncData = collect($teachers)->mapWithKeys(fn ($id) => [
            $id => ['role' => 'teacher'],
        ])->toArray();

        $this->record->teachers()->sync($syncData);
    }
}
