<?php

namespace App\Filament\Resources;
use App\Helpers\FormatHelper;
use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use App\Models\Barbearia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Rules\ValidarTelefone;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Forms\Components\Section;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Gestão';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações do Cliente')
                    ->schema([
                        Select::make('barbearia_id')
                            ->label('Barbearia')
                            ->options(Barbearia::ativas()->pluck('nome', 'id'))
                            ->required()
                            ->searchable(),
                        
                        TextInput::make('nome')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->mask('(99) 99999-9999')
                            ->required()
                            ->rule(new ValidarTelefone()),
                        
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        DatePicker::make('data_nascimento')
                            ->label('Data de Nascimento')
                            ->maxDate(now()),
                    ])
                    ->columns(2),
                
                Section::make('Foto e Observações')
                    ->schema([
                        FileUpload::make('foto')
                            ->label('Foto do Cliente')
                            ->image()
                            ->directory('clientes/fotos')
                            ->maxSize(2048)
                            ->avatar()
                            ->circleCropper()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('500')
                            ->imageResizeTargetHeight('500'),
                        
                        Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(3),
                    ])
                    ->columns(2),
                
                Toggle::make('ativo')
                    ->label('Cliente ativo')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->nome) . '&color=FFFFFF&background=0ea5e9'),
                
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('telefone')
                    ->label('Telefone')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatarTelefone($state))
                    ->searchable(),
                
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('barbearia.nome')
                    ->label('Barbearia')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('idade')
                    ->label('Idade')
                    ->suffix(' anos')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('total_agendamentos')
                    ->label('Total Agend.')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('total_agendamentos_concluidos')
                    ->label('Concluídos')
                    ->sortable()
                    ->toggleable(),
                
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('barbearia_id')
                    ->label('Barbearia')
                    ->options(Barbearia::ativas()->pluck('nome', 'id'))
                    ->searchable(),
                TernaryFilter::make('ativo')
                    ->label('Ativo'),
                Tables\Filters\Filter::make('nome_ou_telefone')
                    ->form([
                        TextInput::make('search')
                            ->label('Buscar por nome ou telefone'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['search'],
                            fn (Builder $query, $search): Builder => $query->where(function ($q) use ($search) {
                                $q->where('nome', 'like', "%{$search}%")
                                  ->orWhere('telefone', 'like', "%{$search}%");
                            }),
                        );
                    }),
                Tables\Filters\Filter::make('idade_min')
                    ->form([
                        TextInput::make('idade_min')
                            ->label('Idade mínima')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['idade_min'],
                            fn (Builder $query, $idade): Builder => $query->whereDate(
                                'data_nascimento',
                                '<=',
                                now()->subYears($idade)->format('Y-m-d')
                            ),
                        );
                    }),
                Tables\Filters\Filter::make('idade_max')
                    ->form([
                        TextInput::make('idade_max')
                            ->label('Idade máxima')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['idade_max'],
                            fn (Builder $query, $idade): Builder => $query->whereDate(
                                'data_nascimento',
                                '>=',
                                now()->subYears($idade)->format('Y-m-d')
                            ),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Action::make('agendamentos')
                    ->label('Agendamentos')
                    ->icon('heroicon-o-calendar')
                    ->url(fn ($record) => route('filament.admin.resources.agendamentos.index', [
                        'tableFilters' => [
                            'cliente_id' => [
                                'value' => $record->id,
                            ],
                        ],
                    ])),
                Action::make('ativar')
                    ->label('Ativar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Cliente $record) {
                        $record->update(['ativo' => true]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Cliente $record) => !$record->ativo && !$record->trashed()),
                Action::make('desativar')
                    ->label('Desativar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function (Cliente $record) {
                        $record->update(['ativo' => false]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Cliente $record) => $record->ativo && !$record->trashed()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('ativar')
                        ->label('Ativar selecionados')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update(['ativo' => true]);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('desativar')
                        ->label('Desativar selecionados')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each->update(['ativo' => false]);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AgendamentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'view' => Pages\ViewCliente::route('/{record}'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
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
        return static::getModel()::ativos()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}