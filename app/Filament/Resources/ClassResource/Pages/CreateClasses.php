<?php

namespace App\Filament\Resources\ClassResource\Pages;

use App\Filament\PlanLimitChecker;
use App\Filament\Resources\ClassResource;
use App\Models\School;
use App\Models\SchoolClass;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateClasses extends CreateRecord
{
    protected static string $resource = ClassResource::class;

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
        PlanLimitChecker::checkLimit($school, 'classes');
    }

    protected function handleRecordCreation(array $data): SchoolClass
    {
        $teachers = $data['teachers'] ?? [];
        unset($data['teachers']);
        if (session()->has('active_school_id')) {
            $data['school_id'] = session('active_school_id');
        } else {
            throw new \Exception('No active school selected.');
        }
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
