<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassResource\Pages;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live()
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    $set('slug', Str::slug($state));
                }),

            Forms\Components\TextInput::make('slug')
                ->hidden()
                ->dehydrated(false),

            // Forms\Components\Select::make('school_id')
            //     ->label('School')
            //     ->options(
            //         School::pluck('name', 'id')
            //     )
            //     ->required(),

            Forms\Components\Select::make('teachers')
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
                Tables\Columns\TextColumn::make('slug')->searchable(),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['owner', 'admin', 'teacher', 'student']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClasses::route('/'),
            'create' => Pages\CreateClasses::route('/create'),
            'edit' => Pages\EditClasses::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user->hasAnyRole(['owner', 'admin'])) {
    $selectedSchoolId = session('selected_school_id');

    if ($selectedSchoolId) {
        return $query->where('school_id', $selectedSchoolId);
    }
}


        // if ($user->hasAnyRole(['owner', 'admin'])) {
        //     $schoolIds = \DB::table('school_user')
        //         ->where('user_id', $user->id)
        //         ->pluck('school_id');

        //     return $query->whereIn('school_id', $schoolIds);
        // }

//         if ($user->hasAnyRole(['owner', 'admin']) && request()->route('record')) {
//     $selectedSchoolId = request()->route('record');

//     return $query->where('school_id', $selectedSchoolId);
// }


        if ($user->hasRole('teacher')) {
            return $query->whereHas('teachers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        // Students: show classes linked to courses they are enrolled in
        if ($user->hasRole('student')) {
            return $query->whereHas('courses', function ($courseQuery) use ($user) {
                $courseQuery->whereHas('users', function ($userQuery) use ($user) {
                    $userQuery
                        ->where('user_id', $user->id)
                        ->where('role', 'student');
                });
            });
        }

        return $query->whereRaw('1=0');
    }
}
