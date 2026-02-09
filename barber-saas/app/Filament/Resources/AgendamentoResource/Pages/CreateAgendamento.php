<?php

namespace App\Filament\Resources\AgendamentoResource\Pages;

use App\Filament\Resources\AgendamentoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAgendamento extends CreateRecord
{
    protected static string $resource = AgendamentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calcular hora_fim baseado na duração do serviço
        if (isset($data['servico_id']) && isset($data['hora_inicio'])) {
            $servico = \App\Models\Servico::find($data['servico_id']);
            if ($servico) {
                $horaInicio = \Carbon\Carbon::parse($data['hora_inicio']);
                $horaFim = $horaInicio->addMinutes($servico->duracao);
                $data['hora_fim'] = $horaFim->format('H:i:s');
                $data['valor'] = $servico->preco;
            }
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Agendamento criado com sucesso!';
    }
}