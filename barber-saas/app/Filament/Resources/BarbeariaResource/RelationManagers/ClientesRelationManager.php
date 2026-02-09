<?php

namespace App\Filament\Resources\BarbeariaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientesRelationManager extends RelationManager
{
    protected static string $relationship = 'clientes';

    protected static ?string $title = 'Clientes da Barbearia';

    protected static ?string $icon = 'heroicon-o-user-group';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telefone')
                    ->mask('(99) 99999-9999')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefone')
                    ->formatStateUsing(fn ($state) => formatarTelefone($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
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
                    ->label('Novo Cliente'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('view_agendamentos')
                    ->label('Agendamentos')
                    ->icon('heroicon-o-calendar')
                    ->url(fn ($record) => \App\Filament\Resources\AgendamentoResource::getUrl('index', [
                        'tableFilters' => [
                            'cliente_id' => [
                                'value' => $record->id,
                            ],
                        ],
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('ativar')
                        ->label('Ativar selecionados')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update(['ativo' => true]);
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('desativar')
                        ->label('Desativar selecionados')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each->update(['ativo' => false]);
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}