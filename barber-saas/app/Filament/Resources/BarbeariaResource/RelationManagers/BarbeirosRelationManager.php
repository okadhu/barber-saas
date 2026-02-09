<?php

namespace App\Filament\Resources\BarbeariaResource\RelationManagers;
use App\Helpers\StatusHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class BarbeirosRelationManager extends RelationManager
{
    protected static string $relationship = 'barbeiros';

    protected static ?string $title = 'Barbeiros da Barbearia';

    protected static ?string $icon = 'heroicon-o-user';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->options(User::whereNotIn('id', function ($query) {
                        $query->select('user_id')
                            ->from('barbearia_user')
                            ->where('barbearia_id', $this->getOwnerRecord()->id);
                    })->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('papel')
                    ->options([
                        'admin' => 'Administrador',
                        'barbeiro' => 'Barbeiro',
                        'recepcionista' => 'Recepcionista',
                    ])
                    ->default('barbeiro')
                    ->required(),
                Forms\Components\Toggle::make('ativo')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('pivot.papel')
                    ->label('Papel')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'admin' => 'Administrador',
                        'barbeiro' => 'Barbeiro',
                        'recepcionista' => 'Recepcionista',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'admin' => 'danger',
                        'barbeiro' => 'primary',
                        'recepcionista' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('pivot.ativo')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('agendamentos_count')
                    ->label('Agendamentos')
                    ->counts('agendamentos'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('papel')
                    ->label('Papel')
                    ->options([
                        'admin' => 'Administrador',
                        'barbeiro' => 'Barbeiro',
                        'recepcionista' => 'Recepcionista',
                    ])
                    ->query(function (Builder $query, $data) {
                        if (!empty($data['value'])) {
                            $query->wherePivot('papel', $data['value']);
                        }
                    }),
                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Ativo')
                    ->queries(
                        true: fn (Builder $query) => $query->wherePivot('ativo', true),
                        false: fn (Builder $query) => $query->wherePivot('ativo', false),
                    ),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Vincular Barbeiro')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Usuário')
                            ->options(User::whereNotIn('id', function ($query) {
                                $query->select('user_id')
                                    ->from('barbearia_user')
                                    ->where('barbearia_id', $this->getOwnerRecord()->id);
                            })->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('papel')
                            ->options([
                                'admin' => 'Administrador',
                                'barbeiro' => 'Barbeiro',
                                'recepcionista' => 'Recepcionista',
                            ])
                            ->default('barbeiro')
                            ->required(),
                        Forms\Components\Toggle::make('ativo')
                            ->default(true),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('papel')
                            ->options([
                                'admin' => 'Administrador',
                                'barbeiro' => 'Barbeiro',
                                'recepcionista' => 'Recepcionista',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('ativo')
                            ->required(),
                    ]),
                Tables\Actions\DetachAction::make()
                    ->label('Remover'),
                Tables\Actions\Action::make('agendamentos')
                    ->label('Agendamentos')
                    ->icon('heroicon-o-calendar')
                    ->url(fn ($record) => \App\Filament\Resources\AgendamentoResource::getUrl('index', [
                        'tableFilters' => [
                            'barbeiro_id' => [
                                'value' => $record->id,
                            ],
                            'barbearia_id' => [
                                'value' => $this->getOwnerRecord()->id,
                            ],
                        ],
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Remover selecionados'),
                    Tables\Actions\BulkAction::make('alterar_papel')
                        ->label('Alterar papel')
                        ->icon('heroicon-o-user-circle')
                        ->form([
                            Forms\Components\Select::make('papel')
                                ->options([
                                    'admin' => 'Administrador',
                                    'barbeiro' => 'Barbeiro',
                                    'recepcionista' => 'Recepcionista',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $this->getOwnerRecord()->barbeiros()->updateExistingPivot($record->id, [
                                    'papel' => $data['papel'],
                                ]);
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}