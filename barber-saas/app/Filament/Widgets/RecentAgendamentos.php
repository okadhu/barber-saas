<?php

namespace App\Filament\Widgets;

use App\Models\Agendamento;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\StatusHelper;
class RecentAgendamentos extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Agendamento::query()
                    ->with(['cliente', 'servico', 'barbeiro'])
                    ->whereDate('data', '>=', now()->format('Y-m-d'))
                    ->orderBy('data')
                    ->orderBy('hora_inicio')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('data')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('hora_inicio')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('servico.nome')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('barbeiro.name')
                    ->label('Barbeiro')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('status')
    ->formatStateUsing(fn ($state) => StatusHelper::getStatusLabel($state))
    ->color(fn ($state) => StatusHelper::getStatusColor($state)),
                
                Tables\Columns\TextColumn::make('valor')
                    ->money('BRL')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $record->confirmar();
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->estaPendente()),
                
                Tables\Actions\Action::make('editar')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => \App\Filament\Resources\AgendamentoResource::getUrl('edit', ['record' => $record])),
            ])
            ->heading('Próximos Agendamentos');
    }
}