<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->required()
                ->email()
                ->unique(User::class, 'email', ignoreRecord: true),

            // TextInput::make('password')
            //     ->password()
            //     ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
            //     ->hiddenOn('edit')
            //     ->maxLength(255)
            //     ->label('Password (leave empty for auto-generation)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
            ])
           ->actions([
            Tables\Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->hasAnyRole(['owner', 'admin'])),
            Tables\Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->hasAnyRole(['owner', 'admin'])),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make()
                ->visible(fn () => auth()->user()?->hasAnyRole(['owner', 'admin'])),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
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
        $schoolId = session('active_school_id');

        return parent::getEloquentQuery()
            ->whereHas('schools', function ($query) use ($schoolId) {
                $query->where('schools.id', $schoolId)
                    ->where('school_user.role', 'student');
            });
    }

    public static function shouldRegisterNavigation(): bool
    {
        $role = session('active_role');

        return in_array($role, ['owner', 'admin','teacher']);
    }
}
