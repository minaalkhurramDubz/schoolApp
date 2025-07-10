<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassResource\Pages;
use App\Models\School;
use App\Models\SchoolClass;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Class name input
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            // Unique slug
            TextInput::make('slug')
                ->required()
                ->unique(SchoolClass::class, 'slug', ignoreRecord: true),

            // Select the school this class belongs to
            Select::make('school_id')
                ->relationship('school', 'name')
                ->required(),
        ]);

    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('slug')->searchable(),
            TextColumn::make('school.name')->label('School'),
        ])
            ->filters([
                //
            ])
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
      public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['owner', 'admin']);
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
        // Tenant scoping: only classes under user's schools
        return parent::getEloquentQuery()
            ->whereHas('school.users', fn ($q) => $q->where('user_id', auth()->id()));
    }
}
