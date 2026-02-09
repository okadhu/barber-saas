<?php

namespace App\Filament\Resources\AgendamentoResource\Pages;

use App\Filament\Resources\AgendamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewAgendamento extends ViewRecord
{
    protected static string $resource = AgendamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('confirmar')
                ->label('Confirmar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $this->record->confirmar();
                    $this->refreshFormData([
                        'status' => 'confirmado',
                        'confirmado_em' => now(),
                    ]);
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->estaPendente()),
            Actions\Action::make('cancelar')
                ->label('Cancelar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('motivo')
                        ->label('Motivo do cancelamento')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->cancelar($data['motivo']);
                    $this->refreshFormData([
                        'status' => 'cancelado',
                        'cancelado_em' => now(),
                        'motivo_cancelamento' => $data['motivo'],
                    ]);
                })
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['pendente', 'confirmado'])),
            Actions\Action::make('iniciar')
                ->label('Iniciar')
                ->icon('heroicon-o-play-circle')
                ->color('warning')
                ->action(function () {
                    $this->record->iniciar();
                    $this->refreshFormData(['status' => 'em_atendimento']);
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->estaConfirmado()),
            Actions\Action::make('concluir')
                ->label('Concluir')
                ->icon('heroicon-o-flag')
                ->color('success')
                ->action(function () {
                    $this->record->concluir();
                    $this->refreshFormData(['status' => 'concluido']);
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'em_atendimento'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informações do Agendamento')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('cliente.nome')
                                    ->label('Cliente')
                                    ->size('lg')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('servico.nome')
                                    ->label('Serviço')
                                    ->badge()
                                    ->color('primary'),
                                
                                Infolists\Components\TextEntry::make('barbeiro.name')
                                    ->label('Barbeiro'),
                                
                                Infolists\Components\TextEntry::make('barbearia.nome')
                                    ->label('Barbearia'),
                                
                                Infolists\Components\TextEntry::make('data_formatada')
                                    ->label('Data'),
                                
                                Infolists\Components\TextEntry::make('horario')
                                    ->label('Horário')
                                    ->formatStateUsing(fn ($record) => 
                                        $record->hora_inicio_formatada . ' - ' . $record->hora_fim_formatada),
                                
                                Infolists\Components\TextEntry::make('valor')
                                    ->label('Valor')
                                    ->money('BRL')
                                    ->weight('bold'),
                                
                                Infolists\Components\BadgeEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => getStatusLabel($state))
                                    ->color(fn ($state) => getStatusColor($state)),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Histórico')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Criado em')
                                    ->dateTime('d/m/Y H:i'),
                                
                                Infolists\Components\TextEntry::make('confirmado_em')
                                    ->label('Confirmado em')
                                    ->dateTime('d/m/Y H:i')
                                    ->hidden(fn ($state) => empty($state)),
                                
                                Infolists\Components\TextEntry::make('cancelado_em')
                                    ->label('Cancelado em')
                                    ->dateTime('d/m/Y H:i')
                                    ->hidden(fn ($state) => empty($state)),
                            ]),
                    ])
                    ->collapsible(),
                
                Infolists\Components\Section::make('Observações')
                    ->schema([
                        Infolists\Components\TextEntry::make('observacoes')
                            ->label('')
                            ->markdown()
                            ->columnSpanFull()
                            ->hidden(fn ($state) => empty($state)),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($state) => empty($this->record->observacoes)),
                
                Infolists\Components\Section::make('Motivo do Cancelamento')
                    ->schema([
                        Infolists\Components\TextEntry::make('motivo_cancelamento')
                            ->label('')
                            ->markdown()
                            ->columnSpanFull()
                            ->hidden(fn ($state) => empty($state)),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($state) => empty($this->record->motivo_cancelamento))
                    ->visible(fn () => $this->record->estaCancelado()),
            ]);
    }
}