<?php


namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            $randomPassword = Str::random(10);
            $data['password'] = Hash::make($randomPassword);

            // Optionally, store it somewhere or email it
            // e.g. session()->flash('generated_password', $randomPassword);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $teacher = $this->record;

        // Assign role
        $teacher->assignRole('teacher');

        // Attach to current school
        $schoolId = session('active_school_id');

        if ($schoolId) {
            $teacher->schools()->attach($schoolId, [
                'role' => 'teacher',
            ]);
        }
    }
}
