<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\PlanLimitChecker;
use App\Filament\Resources\CourseResource;
use App\Models\Course;
use App\Models\School;
use Filament\Resources\Pages\CreateRecord;
use Str;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

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
        PlanLimitChecker::checkLimit($school, 'courses');
    }

    // protected function handleRecordCreation(array $data): Course
    // {
    //     $teachers = $data['teachers'] ?? [];
    //     unset($data['teachers']);

    //     // Generate slug automatically
    //     $data['slug'] = Str::slug($data['name']);

    //     /** @var Course $course */
    //     $course = Course::create($data);

    //     // Attach teachers with pivot role
    //     $syncData = collect($teachers)->mapWithKeys(function ($id) {
    //         return [$id => ['role' => 'teacher']];
    //     })->toArray();

    //     $course->teachers()->sync($syncData);

    //     return $course;
    // }

    protected function handleRecordCreation(array $data): Course
    {
        // Extract teachers and classes
        $teachers = $data['teachers'] ?? [];
        $classes = $data['classes'] ?? [];

        unset($data['teachers'], $data['classes']);

        // Generate slug automatically
        $data['slug'] = Str::slug($data['name']);

        /** @var Course $course */
        $course = Course::create($data);

        // Attach teachers with pivot role
        $syncTeachers = collect($teachers)
            ->mapWithKeys(fn ($id) => [$id => ['role' => 'teacher']])
            ->toArray();

        $course->teachers()->sync($syncTeachers);

        // Attach classes
        $course->classes()->sync($classes);

        return $course;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
