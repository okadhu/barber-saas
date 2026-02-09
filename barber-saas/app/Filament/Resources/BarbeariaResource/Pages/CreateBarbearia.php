<?php

namespace App\Filament\Resources\BarbeariaResource\Pages;

use App\Filament\Resources\BarbeariaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBarbearia extends CreateRecord
{
    protected static string $resource = BarbeariaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Barbearia criada com sucesso!';
    }
}