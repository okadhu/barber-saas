<?php

namespace App\Filament\Resources\BarbeariaResource\Pages;

use App\Filament\Resources\BarbeariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarbearias extends ListRecords
{
    protected static string $resource = BarbeariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova Barbearia')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BarbeariaResource\Widgets\BarbeariaStats::class,
        ];
    }
}