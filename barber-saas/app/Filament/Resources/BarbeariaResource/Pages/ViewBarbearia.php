<?php

namespace App\Filament\Resources\BarbeariaResource\Pages;

use App\Filament\Resources\BarbeariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Helpers\FormatHelper;

class ViewBarbearia extends ViewRecord
{
    protected static string $resource = BarbeariaResource::class;

    protected function getHeaderActions(): array
    {
    return [
        Actions\EditAction::make(),
        Actions\DeleteAction::make(),
        Actions\Action::make('estatisticas')
            ->label('Estatísticas')
            ->icon('heroicon-o-chart-bar')
            ->url(fn () => BarbeariaResource::getUrl('estatisticas', ['record' => $this->record])),
    ];
}

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informações da Barbearia')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('nome')
                                    ->label('Nome')
                                    ->size('lg')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('cnpj')
                                    ->label('CNPJ')
                                    ->formatStateUsing(fn ($state) => FormatHelper::formatarCNPJ($state)),
                                
                                Infolists\Components\TextEntry::make('telefone')
                                    ->label('Telefone')
                                    ->formatStateUsing(fn ($state) => FormatHelper::formatarTelefone($state)),
                                
                                Infolists\Components\TextEntry::make('email')
                                    ->label('E-mail'),
                                
                                Infolists\Components\IconEntry::make('ativo')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->trueColor('success')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->falseColor('danger'),
                            ])->columnSpan(2),
                    ]),
                
                Infolists\Components\Section::make('Endereço')
                    ->schema([
                        Infolists\Components\TextEntry::make('endereco_completo')
                            ->label('Endereço Completo')
                            ->columnSpanFull(),
                    ]),
                
                Infolists\Components\Section::make('Horários de Funcionamento')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('horario_abertura_segunda_sex')
                                    ->label('Segunda a Sexta')
                                    ->formatStateUsing(fn ($record) => 
                                        $record->horario_abertura_segunda_sex . ' às ' . $record->horario_fechamento_segunda_sex),
                                
                                Infolists\Components\TextEntry::make('horario_abertura_sabado')
                                    ->label('Sábado')
                                    ->formatStateUsing(fn ($record) => 
                                        $record->horario_abertura_sabado . ' às ' . $record->horario_fechamento_sabado),
                                
                                Infolists\Components\TextEntry::make('horario_abertura_domingo')
                                    ->label('Domingo')
                                    ->formatStateUsing(function ($record) {
                                        if (!$record->abre_domingo) {
                                            return 'Fechado';
                                        }
                                        return $record->horario_abertura_domingo . ' às ' . $record->horario_fechamento_domingo;
                                    }),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Configurações')
                    ->schema([
                        Infolists\Components\TextEntry::make('tempo_intervalo_agendamento')
                            ->label('Intervalo entre Agendamentos')
                            ->suffix(' minutos'),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i'),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Atualizado em')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),
            ]);
    }
}