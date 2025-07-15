<?php

namespace App\Filament\Resources\InvitesResource\Pages;

use App\Filament\Resources\InvitesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvites extends ListRecords
{
    protected static string $resource = InvitesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
