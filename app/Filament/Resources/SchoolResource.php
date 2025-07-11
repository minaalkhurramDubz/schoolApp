<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Models\School;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('slug')
                ->required()
                ->unique(School::class, 'slug', ignoreRecord: true),

            Select::make('plan_id')
                ->relationship('plan', 'name')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('plan.name')->label('Plan'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['owner', 'admin'])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['owner', 'admin'])) {
            return parent::getEloquentQuery()
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
        }

        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['owner', 'admin']);
    }
}
