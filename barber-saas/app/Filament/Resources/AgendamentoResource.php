<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgendamentoResource\Pages;
use App\Models\Agendamento;
use App\Models\Barbearia;
use App\Models\Cliente;
use App\Models\Servico;
use App\Models\User;
use App\Rules\ValidarHorarioDisponivel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Helpers\StatusHelper;

class AgendamentoResource extends Resource
{
    protected static ?string $model = Agendamento::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Gestão';

    protected static ?string $navigationLabel = 'Agendamentos';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Agendamento')
                    ->schema([
                        Forms\Components\Select::make('barbearia_id')
                            ->label('Barbearia')
                            ->options(Barbearia::ativas()->pluck('nome', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('servico_id', null)),
                        
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(function ($get) {
                                $barbeariaId = $get('barbearia_id');
                                if (!$barbeariaId) {
                                    return [];
                                }
                                return Cliente::daBarbearia($barbeariaId)
                                    ->ativos()
                                    ->pluck('nome', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nome')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('telefone')
                                    ->required()
                                    ->mask('(99) 99999-9999'),
                                Forms\Components\Hidden::make('barbearia_id')
                                    ->default(fn ($get) => $get('barbearia_id')),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Cliente::create($data)->id;
                            }),
                        
                        Forms\Components\Select::make('servico_id')
                            ->label('Serviço')
                            ->options(function ($get) {
                                $barbeariaId = $get('barbearia_id');
                                if (!$barbeariaId) {
                                    return [];
                                }
                                return Servico::daBarbearia($barbeariaId)
                                    ->ativos()
                                    ->pluck('nome', 'id');
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $servico = Servico::find($state);
                                if ($servico) {
                                    $set('valor', $servico->preco);
                                    $set('duracao', $servico->duracao);
                                    // Calcula hora_fim automaticamente
                                    if ($get('hora_inicio')) {
                                        $horaFim = \Carbon\Carbon::parse($get('hora_inicio'))
                                            ->addMinutes($servico->duracao)
                                            ->format('H:i');
                                        $set('hora_fim', $horaFim);
                                    }
                                }
                            }),
                        
                        Forms\Components\Select::make('barbeiro_id')
                            ->label('Barbeiro')
                            ->options(function ($get) {
                                $barbeariaId = $get('barbearia_id');
                                if (!$barbeariaId) {
                                    return [];
                                }
                                
                                // CORREÇÃO AQUI: Usar uma query mais específica
                                return User::whereHas('barbearias', function ($query) use ($barbeariaId) {
                                    $query->where('barbearia_id', $barbeariaId);
                                })
                                ->select('id', 'name') // Selecionar explicitamente as colunas
                                ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\DatePicker::make('data')
                            ->label('Data')
                            ->required()
                            ->minDate(now())
                            ->default(now()),
                        
                        Forms\Components\TimePicker::make('hora_inicio')
                            ->label('Hora Início')
                            ->required()
                            ->seconds(false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state && $get('duracao')) {
                                    $horaFim = \Carbon\Carbon::parse($state)
                                        ->addMinutes($get('duracao'))
                                        ->format('H:i');
                                    $set('hora_fim', $horaFim);
                                }
                            })
                            ->rules([
                                function ($get) {
                                    return new ValidarHorarioDisponivel(
                                        barbeariaId: $get('barbearia_id'),
                                        data: $get('data'),
                                        duracao: $get('duracao') ?? 30,
                                        barbeiroId: $get('barbeiro_id'),
                                        agendamentoId: $get('id') // para edição
                                    );
                                }
                            ]),
                        
                        Forms\Components\Hidden::make('hora_fim'),
                        Forms\Components\Hidden::make('valor'),
                        Forms\Components\Hidden::make('duracao'),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pendente' => 'Pendente',
                                'confirmado' => 'Confirmado',
                                'em_atendimento' => 'Em Atendimento',
                                'concluido' => 'Concluído',
                                'cancelado' => 'Cancelado',
                                'nao_compareceu' => 'Não Compareceu',
                            ])
                            ->default('pendente')
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(3),
                        
                        Forms\Components\Textarea::make('motivo_cancelamento')
                            ->label('Motivo do Cancelamento')
                            ->rows(2)
                            ->visible(fn ($get) => $get('status') === 'cancelado'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data_formatada')
                    ->label('Data')
                    ->sortable(['data'])
                    ->searchable(['data']),
                
                Tables\Columns\TextColumn::make('hora_inicio_formatada')
                    ->label('Hora')
                    ->sortable(['hora_inicio']),
                
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('servico.nome')
                    ->label('Serviço')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('barbeiro.name')
                    ->label('Barbeiro')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => StatusHelper::getStatusLabel($state))
                    ->color(fn ($state) => StatusHelper::getStatusColor($state)),
                
                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('barbearia_id')
                    ->label('Barbearia')
                    ->options(Barbearia::ativas()->pluck('nome', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'confirmado' => 'Confirmado',
                        'em_atendimento' => 'Em Atendimento',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        'nao_compareceu' => 'Não Compareceu',
                    ]),
                Tables\Filters\SelectFilter::make('barbeiro_id')
                    ->label('Barbeiro')
                    ->options(function () {
                        // CORREÇÃO AQUI também: especificar a tabela
                        return User::whereIn('users.id', function ($query) {
                            $query->select('barbeiro_id')
                                  ->from('agendamentos')
                                  ->groupBy('barbeiro_id');
                        })->pluck('users.name', 'users.id');
                    })
                    ->searchable(),
                Tables\Filters\Filter::make('data')
                    ->form([
                        Forms\Components\DatePicker::make('data_inicio')
                            ->label('Data início'),
                        Forms\Components\DatePicker::make('data_fim')
                            ->label('Data fim'),
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
                Tables\Filters\Filter::make('hoje')
                    ->label('Apenas hoje')
                    ->query(fn (Builder $query): Builder => $query->hoje()),
                Tables\Filters\Filter::make('futuros')
                    ->label('Futuros')
                    ->query(fn (Builder $query): Builder => $query->futuros()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Agendamento $record) {
                        $record->confirmar();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Agendamento $record) => $record->estaPendente()),
                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo do cancelamento')
                            ->required(),
                    ])
                    ->action(function (Agendamento $record, array $data) {
                        $record->cancelar($data['motivo']);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Agendamento $record) => in_array($record->status, ['pendente', 'confirmado'])),
                Tables\Actions\Action::make('iniciar')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play-circle')
                    ->color('warning')
                    ->action(function (Agendamento $record) {
                        $record->iniciar();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Agendamento $record) => $record->estaConfirmado()),
                Tables\Actions\Action::make('concluir')
                    ->label('Concluir')
                    ->icon('heroicon-o-flag')
                    ->color('success')
                    ->action(function (Agendamento $record) {
                        $record->concluir();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Agendamento $record) => $record->status === 'em_atendimento'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgendamentos::route('/'),
            'create' => Pages\CreateAgendamento::route('/create'),
            'view' => Pages\ViewAgendamento::route('/{record}'),
            'edit' => Pages\EditAgendamento::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('data', today())->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::whereDate('data', today())->count();
        return $count > 0 ? 'success' : 'gray';
    }
}