<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $roles = $data['roles'] ?? [];
        $schoolId = $data['school_id'] ?? null;

        if (auth()->user()->hasRole('admin') && in_array('owner', $roles)) {
            Notification::make()
                ->title('Not Allowed')
                ->body('Admin does not have permission for this action!')
                ->warning()
                ->send();
            throw \Illuminate\Validation\ValidationException::withMessages([
                'roles' => 'Admins are not allowed to assign the "owner" role.',
            ]);
        }

        unset($data['roles'], $data['school_id']);

        $user = new User;
        $user->fill($data);
        $user->save();

        // if (! empty($roles)) {
        //     $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roles)
        //         ->pluck('name')
        //         ->toArray();

        //     $user->syncRoles($roleNames);
        // }

        if (! empty($roles)) {
            $user->syncRoles($roles);
        }

        if ($schoolId) {
            $user->schools()->attach($schoolId, [
                'role' => $roleNames[0] ?? 'student', // default role if none picked
            ]);
        }

        return $user;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
