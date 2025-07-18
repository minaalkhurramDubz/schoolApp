<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use App\Models\School;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('slug')
                ->hidden()                  // hide the slug field from the user
                ->dehydrated(false),        // exclude from automatic saving

            Forms\Components\Select::make('school_id')
                ->label('School')
                ->options(
                    School::pluck('name', 'id')
                )
                ->required(),

            Select::make('classes')
                ->label('Assigned Classes')
                ->multiple()
                ->relationship('classes', 'name')
                ->preload()
                ->searchable(),

            Select::make('teachers')
                ->label('Assigned Teachers')
                ->multiple()
                ->options(
                    User::whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
                        ->pluck('name', 'id')
                )
                ->preload()
                ->searchable()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                // Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('school.name')->label('School'),
                Tables\Columns\TextColumn::make('teachers.name')
                    ->label('Teachers')
                    ->listWithLineBreaks(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['owner', 'admin', 'teacher'])),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['owner', 'admin', 'teacher'])),

                Tables\Actions\Action::make('Unenroll')
                    ->label('Unenroll')
                    ->icon('heroicon-o-minus')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => auth()->user()?->hasRole('student'))
                    ->action(function ($record) {
                        $user = auth()->user();

                        // Remove the row from course_user
                        $record->users()->detach($user->id);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Unenrolled Successfully')
                            ->send();

                        // $this->redirect(\App\Filament\Resources\CourseResource::getUrl());
                    }),
            ]);

    }

    public static function getRelations(): array
    {
        return [];
    }

    public function unenrollStudent(Course $course): void
    {
        $user = auth()->user();

        // Remove the row from course_user
        $course->users()
            ->wherePivot('role', 'student')
            ->detach($user->id);

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Unenrolled Successfully')
            ->send();

        $this->redirect(\App\Filament\Resources\CourseResource::getUrl());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit')];
    }

//     public static function getEloquentQuery(): Builder
//     {
//         $query = parent::getEloquentQuery();

//         $user = auth()->user();

//         // // Owner and admin see all courses in their schools
//         // if ($user->hasAnyRole(['owner', 'admin'])) {
//         //     $schoolIds = \DB::table('school_user')
//         //         ->where('user_id', $user->id)
//         //         ->pluck('school_id');

//         //     return $query->whereIn('school_id', $schoolIds);
//         // }

//         if ($user->hasAnyRole(['owner', 'admin'])) {
//     $selectedSchoolId = session('selected_school_id');

//     return $query->where('school_id', $selectedSchoolId);
// }

//         // Teachers see only courses they teach
//         if ($user->hasRole('teacher')) {
//             return $query->whereHas('teachers', function ($q) use ($user) {
//                 $q->where('user_id', $user->id);
//             });
//         }

//         // Teachers see only courses they teach
//         if ($user->hasRole('student')) {
//             return $query->whereHas('students', function ($q) use ($user) {
//                 $q->where('user_id', $user->id);
//             });
//         }

//         return $query->whereRaw('1=0');
//     }


public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    // Owner and admin see courses for the selected school
    if ($user->hasAnyRole(['owner', 'admin'])) {
        $selectedSchoolId = session('selected_school_id');

        if ($selectedSchoolId) {
            return $query->where('school_id', $selectedSchoolId);
        } else {
            // fallback: show nothing if no school is selected
            return $query->whereRaw('1=0');
        }
    }

    // Teachers see only the courses they teach
    if ($user->hasRole('teacher')) {
        return $query->whereHas('teachers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    // Students see only the courses they are enrolled in
    if ($user->hasRole('student')) {
        return $query->whereHas('students', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    // Default fallback: show nothing
    return $query->whereRaw('1=0');
}


    public static function canViewAny(): bool
    {
        return auth()->check() && session()->has('active_role');
    }
}
