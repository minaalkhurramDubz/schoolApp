<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class TeacherResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Teacher';

    protected static ?string $pluralLabel = 'Teacher';

    protected static ?string $navigationLabel = 'Teacher';

    protected static ?string $slug = 'Teacher';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Basic user info
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->required()->email()->unique(User::class, 'email', ignoreRecord: true),

            // Role assignment
            Select::make('roles')
                ->multiple()
                ->options(Role::all()->pluck('name', 'name')->toArray())
                ->required(),
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $role = session('active_role');

        return in_array($role, ['teacher', 'admin', 'owner']);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('roles.name')->label('Roles'),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {

        $query = parent::getEloquentQuery();

        if (session()->has('active_school_id')) {
            $schoolId = session('active_school_id');

            return $query->whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId)
                    ->where('school_user.role', 'teacher');
            });
        }

        // Optional: return empty result if no school picked
        return $query->whereRaw('0=1');
    }

    // public static function canViewAny(): bool
    // {

    //     return auth()->check() && session()->has('active_role');
    // }
}
