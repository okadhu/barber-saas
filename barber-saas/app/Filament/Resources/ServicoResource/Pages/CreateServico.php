<?php

namespace App\Filament\Resources\ServicoResource\Pages;

use App\Filament\Resources\ServicoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServico extends CreateRecord
{
    protected static string $resource = ServicoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Serviço criado com sucesso!';
    }
}