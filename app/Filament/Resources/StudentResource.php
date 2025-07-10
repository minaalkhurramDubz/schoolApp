<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
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

class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Student';

    protected static ?string $pluralLabel = 'Students';

    protected static ?string $navigationLabel = 'Students';

    protected static ?string $slug = 'students';

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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $schoolId = session('active_school_id');

        // Allow only specific roles to see the students list
        if (! in_array(session('active_role'), ['owner', 'admin', 'teacher'])) {
            abort(403); // Or return an empty query if you'd rather silently hide
        }

        // Return students that belong to the selected school
        return parent::getEloquentQuery()
            ->whereHas('schools', function ($query) use ($schoolId) {
                $query->where('schools.id', $schoolId);
            })
            ->whereHas('roles', fn ($q) => $q->where('name', 'student'));
    }
}
