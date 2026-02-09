<?php

namespace App\Filament\Resources\ServicoResource\Pages;

use App\Filament\Resources\ServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewServico extends ViewRecord
{
    protected static string $resource = ServicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informações do Serviço')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\ImageEntry::make('imagem')
                                ->label('Imagem')
                                ->square()
                                ->size(150)
                                ->defaultImageUrl('https://via.placeholder.com/150?text=S')
                                ->columnSpan(1),
                            
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('nome')
                                        ->label('Nome do Serviço')
                                        ->size('lg')
                                        ->weight('bold'),
                                    
                                    Infolists\Components\TextEntry::make('preco')
                                        ->label('Preço')
                                        ->money('BRL')
                                        ->size('lg'),
                                    
                                    Infolists\Components\TextEntry::make('duracao_formatada')
                                        ->label('Duração'),
                                    
                                    Infolists\Components\TextEntry::make('barbearia.nome')
                                        ->label('Barbearia'),
                                    
                                    Infolists\Components\TextEntry::make('ordem')
                                        ->label('Ordem de Exibição')
                                        ->badge(),
                                    
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
                
                Infolists\Components\Section::make('Descrição')
                    ->schema([
                        Infolists\Components\TextEntry::make('descricao')
                            ->label('')
                            ->markdown()
                            ->columnSpanFull()
                            ->hidden(fn ($state) => empty($state)),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($state) => empty($this->record->descricao)),
                
                Infolists\Components\Section::make('Estatísticas')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_agendamentos')
                                    ->label('Total Agendamentos')
                                    ->badge()
                                    ->color('primary'),
                                
                                Infolists\Components\TextEntry::make('receita_total')
                                    ->label('Receita Total')
                                    ->money('BRL')
                                    ->color('success'),
                                
                                Infolists\Components\TextEntry::make('media_mensal')
                                    ->label('Média Mensal')
                                    ->formatStateUsing(function ($record) {
                                        $total = $record->total_agendamentos;
                                        $meses = max(1, floor($record->created_at->diffInMonths(now())));
                                        $media = $total / $meses;
                                        return round($media, 1) . ' agendamentos/mês';
                                    })
                                    ->color('info'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Informações Adicionais')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Criado em')
                                    ->dateTime('d/m/Y H:i'),
                                
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Atualizado em')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),
            ]);
    }
}