<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $roles = $this->data['roles'] ?? [];
        $schoolId = $this->data['school_id'] ?? null;

        // Save global roles via Spatie
        $this->record->syncRoles($roles);

        if ($schoolId) {
            // detach old roles for this user+school
            $this->record->schools()->wherePivot('school_id', $schoolId)->detach();

            // attach new roles
            foreach ($roles as $role) {
                $this->record->schools()->attach($schoolId, [
                    'role' => $role,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
