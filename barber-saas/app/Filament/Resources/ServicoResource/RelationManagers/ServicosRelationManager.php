<?php

namespace App\Filament\Resources\BarbeariaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServicosRelationManager extends RelationManager
{
    protected static string $relationship = 'servicos';

    protected static ?string $title = 'Serviços da Barbearia';

    protected static ?string $icon = 'heroicon-o-scissors';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descricao')
                    ->rows(3),
                Forms\Components\TextInput::make('preco')
                    ->numeric()
                    ->prefix('R$')
                    ->required(),
                Forms\Components\TextInput::make('duracao')
                    ->numeric()
                    ->suffix('minutos')
                    ->required(),
                Forms\Components\Toggle::make('ativo')
                    ->default(true),
                Forms\Components\TextInput::make('ordem')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('preco')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duracao')
                    ->formatStateUsing(fn ($state) => $state . ' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ordem')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_agendamentos')
                    ->label('Agendamentos')
                    ->sortable(),
                Tables\Columns\IconColumn::make('ativo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Ativo'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Serviço'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('reordenar')
                        ->label('Reordenar')
                        ->icon('heroicon-o-arrows-up-down')
                        ->form([
                            Forms\Components\TextInput::make('nova_ordem')
                                ->label('Nova ordem')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each->update(['ordem' => $data['nova_ordem']]);
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}