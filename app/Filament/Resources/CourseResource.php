<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Course name
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            // Slug for friendly URLs
            TextInput::make('slug')
                ->required()
                ->unique(Course::class, 'slug', ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('name')->searchable(),
            TextColumn::make('slug')->searchable(),
        ];

        $role = session('active_role');

        if (in_array($role, ['owner', 'admin', 'student'])) {
            $columns[] = TextColumn::make('teachers')
                ->label('Taught By')
                ->getStateUsing(fn ($record) => $record->users()
                    ->wherePivot('role', 'teacher')
                    ->pluck('name')
                    ->join(', ')
                );
        }

        return $table->columns($columns)
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $schoolId = session('active_school_id');
        $role = session('active_role');

        if (in_array($role, ['owner', 'admin'])) {
            // Owners and admins see ALL courses in their school
            return parent::getEloquentQuery()
                ->where('school_id', $schoolId);
        }

        if ($role === 'teacher') {
            // Teachers see only courses they teach
            return parent::getEloquentQuery()
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->where('role', 'teacher');
                });
        }

        if ($role === 'student') {
            // Students see only courses they belong to
            return parent::getEloquentQuery()
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->where('role', 'student');
                });
        }

        // Default, show nothing if no role
        return parent::getEloquentQuery()->whereRaw('1=0');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['owner', 'admin','teacher','student']);
    }
}
