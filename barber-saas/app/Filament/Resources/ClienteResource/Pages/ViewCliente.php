<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCliente extends ViewRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('novo_agendamento')
                ->label('Novo Agendamento')
                ->icon('heroicon-o-calendar')
                ->url(fn () => \App\Filament\Resources\AgendamentoResource::getUrl('create', [
                    'cliente_id' => $this->record->id,
                ])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informações do Cliente')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\ImageEntry::make('foto')
                                ->label('Foto')
                                ->circular()
                                ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->nome) . '&color=FFFFFF&background=0ea5e9')
                                ->columnSpan(1),
                            
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('nome')
                                        ->label('Nome Completo')
                                        ->size('lg')
                                        ->weight('bold'),
                                    
                                    Infolists\Components\TextEntry::make('idade')
                                        ->label('Idade')
                                        ->suffix(' anos'),
                                    
                                    Infolists\Components\TextEntry::make('telefone')
                                        ->label('Telefone')
                                        ->formatStateUsing(fn ($state) => formatarTelefone($state)),
                                    
                                    Infolists\Components\TextEntry::make('email')
                                        ->label('E-mail'),
                                    
                                    Infolists\Components\TextEntry::make('barbearia.nome')
                                        ->label('Barbearia'),
                                    
                                    Infolists\Components\IconEntry::make('ativo')
                                        ->label('Status')
                                        ->boolean()
                                        ->trueIcon('heroicon-o-check-circle')
                                        ->trueColor('success')
                                        ->falseIcon('heroicon-o-x-circle')
                                        ->falseColor('danger'),
                                ])->columnSpan(2),
                        ]),
                    ]),
                
                Infolists\Components\Section::make('Detalhes')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('data_nascimento')
                                    ->label('Data de Nascimento')
                                    ->date('d/m/Y'),
                                
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Cadastrado em')
                                    ->dateTime('d/m/Y H:i'),
                                
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Atualizado em')
                                    ->dateTime('d/m/Y H:i'),
                                
                                Infolists\Components\TextEntry::make('deleted_at')
                                    ->label('Excluído em')
                                    ->dateTime('d/m/Y H:i')
                                    ->hidden(fn ($record) => !$record->deleted_at),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Estatísticas')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_agendamentos')
                                    ->label('Total Agendamentos')
                                    ->badge()
                                    ->color('primary'),
                                
                                Infolists\Components\TextEntry::make('total_agendamentos_concluidos')
                                    ->label('Agend. Concluídos')
                                    ->badge()
                                    ->color('success'),
                                
                                Infolists\Components\TextEntry::make('total_gasto')
                                    ->label('Total Gasto')
                                    ->money('BRL')
                                    ->color('success'),
                                
                                Infolists\Components\TextEntry::make('taxa_comparecimento')
                                    ->label('Taxa Comparecimento')
                                    ->formatStateUsing(function ($record) {
                                        $total = $record->total_agendamentos;
                                        $concluidos = $record->total_agendamentos_concluidos;
                                        
                                        if ($total == 0) {
                                            return '0%';
                                        }
                                        
                                        $percentual = round(($concluidos / $total) * 100, 2);
                                        return $percentual . '%';
                                    })
                                    ->color(fn ($state) => 
                                        floatval(str_replace('%', '', $state)) >= 80 ? 'success' : 'warning'),
                            ]),
                    ]),
                
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
            ]);
    }
}