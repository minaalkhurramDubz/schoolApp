<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\PlanLimitChecker;
use App\Filament\Resources\StudentResource;
use App\Models\School;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function beforeCreate(): void
    {
        $schoolId = session('active_school_id');

        if (! $schoolId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'school_id' => 'No active school selected.',
            ]);
        }

        $school = School::findOrFail($schoolId);

        // ✅ Call your plan checker like this:
        PlanLimitChecker::checkLimit($school, 'students');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            $randomPassword = Str::random(10);
            $data['password'] = Hash::make($randomPassword);

            session()->flash('generated_password', $randomPassword);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $student = $this->record;

        // assign student role
        $student->assignRole('student');

        // attach to current school
        $schoolId = session('active_school_id');

        if ($schoolId) {
            $student->schools()->attach($schoolId, [
                'role' => 'student',
            ]);
        }

        if (session()->has('generated_password')) {
            Notification::make()
                ->title('Student created')
                ->body('Temporary password: '.session()->pull('generated_password'))
                ->success()
                ->send();
        }
        
    }
      protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
