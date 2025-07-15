<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        // return [
        //     Actions\CreateAction::make(),
        // ];

        return [
            Actions\CreateAction::make(),
            // Action::make('Invite User')
            //     ->icon('heroicon-o-plus')
            //     ->url(UserResource::getUrl('invite'))
            //     ->visible(fn () => auth()->user()->hasRole('owner')),
        ];
    }
}
