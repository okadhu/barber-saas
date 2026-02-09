<?php

namespace App\Filament\Resources\BarbeariaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgendamentosRelationManager extends RelationManager
{
    protected static string $relationship = 'agendamentos';

    protected static ?string $title = 'Agendamentos da Barbearia';

    protected static ?string $icon = 'heroicon-o-calendar';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cliente_id')
                    ->relationship('cliente', 'nome')
                    ->required(),
                Forms\Components\Select::make('servico_id')
                    ->relationship('servico', 'nome')
                    ->required(),
                Forms\Components\DatePicker::make('data')
                    ->required(),
                Forms\Components\TimePicker::make('hora_inicio')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pendente' => 'Pendente',
                        'confirmado' => 'Confirmado',
                        'em_atendimento' => 'Em Atendimento',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        'nao_compareceu' => 'Não Compareceu',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('data')
            ->columns([
                Tables\Columns\TextColumn::make('data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora_inicio')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('servico.nome'),
                Tables\Columns\TextColumn::make('barbeiro.name')
                    ->label('Barbeiro'),
                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => getStatusLabel($state))
                    ->color(fn ($state) => getStatusColor($state)),
                Tables\Columns\TextColumn::make('valor')
                    ->money('BRL'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pendente' => 'Pendente',
                        'confirmado' => 'Confirmado',
                        'em_atendimento' => 'Em Atendimento',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        'nao_compareceu' => 'Não Compareceu',
                    ]),
                Tables\Filters\Filter::make('data')
                    ->form([
                        Forms\Components\DatePicker::make('data_inicio'),
                        Forms\Components\DatePicker::make('data_fim'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['data_inicio'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data', '>=', $date),
                            )
                            ->when(
                                $data['data_fim'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Agendamento'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $record->confirmar();
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->estaPendente()),
                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function ($record) {
                        $record->cancelar('Cancelado pelo administrador');
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['pendente', 'confirmado'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('confirmar_varios')
                        ->label('Confirmar selecionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->confirmar();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('cancelar_varios')
                        ->label('Cancelar selecionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->cancelar('Cancelamento em massa');
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}