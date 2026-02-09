<?php

namespace App\Filament\Widgets;

use App\Models\Barbearia;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BarbeariaOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Barbearia::query()
                    ->withCount(['clientes', 'servicos', 'agendamentos' => function ($query) {
                        $query->whereDate('data', now()->format('Y-m-d'));
                    }])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('cidade')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('clientes_count')
                    ->label('Clientes')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('servicos_count')
                    ->label('Serviços')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('agendamentos_count')
                    ->label('Agend. Hoje')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('ativo')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => \App\Filament\Resources\BarbeariaResource::getUrl('view', ['record' => $record])),
            ])
            ->heading('Visão Geral das Barbearias');
    }
}