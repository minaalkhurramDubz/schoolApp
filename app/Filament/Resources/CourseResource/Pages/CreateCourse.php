<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Models\Course;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Str;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function handleRecordCreation(array $data): Course
    {
        $teachers = $data['teachers'] ?? [];
        unset($data['teachers']);

        // Generate slug automatically
        $data['slug'] = Str::slug($data['name']);

        /** @var Course $course */
        $course = Course::create($data);

        // Attach teachers with pivot role
        $syncData = collect($teachers)->mapWithKeys(function ($id) {
            return [$id => ['role' => 'teacher']];
        })->toArray();

        $course->teachers()->sync($syncData);

        return $course;
    }
}
