<?php

namespace App\Filament\Resources\AgendamentoResource\Pages;

use App\Filament\Resources\AgendamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgendamento extends EditRecord
{
    protected static string $resource = AgendamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Agendamento atualizado com sucesso!';
    }
}