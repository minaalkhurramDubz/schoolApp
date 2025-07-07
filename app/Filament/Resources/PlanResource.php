<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
          return $form->schema([
            // Plan name
            TextInput::make('name')
                ->required()
                ->maxLength(100),

            // Numeric limits
            TextInput::make('max_schools')->numeric()->required(),
            TextInput::make('max_classes')->numeric()->required(),
            TextInput::make('max_teachers')->numeric()->required(),
            TextInput::make('max_students')->numeric()->required(),
            TextInput::make('max_courses')->numeric()->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('max_schools')->label('Schools'),
            TextColumn::make('max_classes')->label('Classes'),
            TextColumn::make('max_teachers')->label('Teachers'),
            TextColumn::make('max_students')->label('Students'),
            TextColumn::make('max_courses')->label('Courses'),
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
