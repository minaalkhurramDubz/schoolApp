<?php

namespace App\Filament\Resources\InvitesResource\Pages;

use App\Filament\Resources\InvitesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvites extends EditRecord
{
    protected static string $resource = InvitesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
