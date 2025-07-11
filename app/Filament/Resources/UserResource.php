<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')
                ->required()
                ->email()
                ->unique(User::class, 'email', ignoreRecord: true),
            TextInput::make('password')
                ->password()
                ->required(fn (string $context) => $context === 'create')
                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                ->label('Password'),

            // keys = names, values = names
            Select::make('roles')
                ->multiple()
                ->options(Role::all()->pluck('name', 'name')->toArray()),

        ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && session()->has('active_role');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['owner', 'admin']);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('roles.name')->label('Roles'),
        ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn () => auth()->user()->hasRole('owner')),
                Tables\Actions\DeleteAction::make()->visible(fn () => auth()->user()->hasRole('owner')),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('owner')) {
            // find the school ids this owner belongs to
            $schoolIds = \DB::table('school_user')
                ->where('user_id', $user->id)
                ->where('role', 'owner')
                ->pluck('school_id');

            return parent::getEloquentQuery()
                ->whereHas('schools', function ($query) use ($schoolIds) {
                    $query->whereIn('schools.id', $schoolIds);
                });
        } elseif ($user->hasRole('admin')) {
            // find the school ids this owner belongs to
            $schoolIds = \DB::table('school_user')
                ->where('user_id', $user->id)
                ->where('role', 'admin')
                ->pluck('school_id');

            return parent::getEloquentQuery()
                ->whereHas('schools', function ($query) use ($schoolIds) {
                    $query->whereIn('schools.id', $schoolIds);
                });
        }

        // optionally add logic for admins or others here

        return parent::getEloquentQuery();
    }
}
