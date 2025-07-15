<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {

        $user = auth()->user();
        if ($user->hasRole('student')) {
            return [
                Actions\Action::make('enroll')
                    ->label('Enroll in Course')
                    ->icon('heroicon-o-plus')
                    ->action(fn ($data) => $this->enrollStudent($data))
                    ->form([
                        \Filament\Forms\Components\Select::make('course_id')
                            ->label('Select Course')
                            ->options(
                                \App\Models\Course::query()
                                    ->whereIn('school_id', $user->schools()->pluck('school_id'))
                                    ->pluck('name', 'id')
                            )
                            ->required(),
                    ]),
            ];
        }

        return [
            Actions\CreateAction::make(),

        ];
    }

    public function enrollStudent(array $data): void
    {
        $user = auth()->user();

        $courseId = $data['course_id'];

        $course = \App\Models\Course::findOrFail($courseId);
        $course->users()->syncWithoutDetaching([
            $user->id => ['role' => 'student'],
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Enrolled')
            ->body('successfully enrolled')
            ->success()
            ->send();
        // Redirect or reload page
        $this->redirect(CourseResource::getUrl());

    }
}
