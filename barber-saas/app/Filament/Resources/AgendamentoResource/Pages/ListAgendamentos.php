<?php

namespace App\Filament\Resources\AgendamentoResource\Pages;

use App\Filament\Resources\AgendamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgendamentos extends ListRecords
{
    protected static string $resource = AgendamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Agendamento')
                ->icon('heroicon-o-calendar'), // CORRIGIDO: calendar-plus → calendar
        ];

    }

    protected function getHeaderWidgets(): array
    {
        return [
            AgendamentoResource\Widgets\AgendamentoStats::class,
        ];
    }
}