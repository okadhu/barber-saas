<?php

namespace App\Filament\Resources\BarbeariaResource\Pages;

use App\Filament\Resources\BarbeariaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarbearia extends EditRecord
{
    protected static string $resource = BarbeariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Barbearia atualizada com sucesso!';
    }
}