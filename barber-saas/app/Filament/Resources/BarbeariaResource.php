<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarbeariaResource\Pages;
use App\Filament\Resources\BarbeariaResource\RelationManagers;
use App\Models\Barbearia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Rules\ValidarCNPJ;
use App\Rules\ValidarTelefone;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;

class BarbeariaResource extends Resource
{
    protected static ?string $model = Barbearia::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Barbearias';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Informações Básicas')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Dados da Barbearia')
                                    ->schema([
                                        TextInput::make('nome')
                                            ->label('Nome da Barbearia')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        TextInput::make('email')
                                            ->label('E-mail')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        
                                        TextInput::make('telefone')
                                            ->label('Telefone')
                                            ->mask('(99) 99999-9999')
                                            ->required()
                                            ->rule(new ValidarTelefone()),
                                        
                                        TextInput::make('cnpj')
                                            ->label('CNPJ')
                                            ->mask('99.999.999/9999-99')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->rule(new ValidarCNPJ()),
                                    ])
                                    ->columns(2),
                                    
                                Section::make('Endereço')
                                    ->schema([
                                        TextInput::make('cep')
                                            ->label('CEP')
                                            ->mask('99999-999')
                                            ->required(),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('endereco')
                                                    ->label('Endereço')
                                                    ->required(),
                                                
                                                TextInput::make('numero')
                                                    ->label('Número')
                                                    ->required(),
                                            ]),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('bairro')
                                                    ->label('Bairro')
                                                    ->required(),
                                                
                                                TextInput::make('cidade')
                                                    ->label('Cidade')
                                                    ->required(),
                                            ]),
                                            
                                        TextInput::make('estado')
                                            ->label('Estado')
                                            ->maxLength(2)
                                            ->required(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Horários de Funcionamento')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Segunda a Sexta')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TimePicker::make('horario_abertura_segunda_sex')
                                                    ->label('Abertura')
                                                    ->required()
                                                    ->seconds(false),
                                                
                                                TimePicker::make('horario_fechamento_segunda_sex')
                                                    ->label('Fechamento')
                                                    ->required()
                                                    ->seconds(false),
                                            ]),
                                    ]),
                                    
                                Section::make('Sábado')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TimePicker::make('horario_abertura_sabado')
                                                    ->label('Abertura')
                                                    ->required()
                                                    ->seconds(false),
                                                
                                                TimePicker::make('horario_fechamento_sabado')
                                                    ->label('Fechamento')
                                                    ->required()
                                                    ->seconds(false),
                                            ]),
                                    ]),
                                    
                                Section::make('Domingo')
                                    ->schema([
                                        Toggle::make('abre_domingo')
                                            ->label('Abre aos domingos?')
                                            ->reactive(),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TimePicker::make('horario_abertura_domingo')
                                                    ->label('Abertura')
                                                    ->hidden(fn ($get) => !$get('abre_domingo'))
                                                    ->seconds(false),
                                                
                                                TimePicker::make('horario_fechamento_domingo')
                                                    ->label('Fechamento')
                                                    ->hidden(fn ($get) => !$get('abre_domingo'))
                                                    ->seconds(false),
                                            ]),
                                    ]),
                                    
                                TextInput::make('tempo_intervalo_agendamento')
                                    ->label('Intervalo entre agendamentos (minutos)')
                                    ->numeric()
                                    ->minValue(15)
                                    ->maxValue(120)
                                    ->default(30),
                            ]),
                            
                        Tabs\Tab::make('Logo e Status')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Logo da Barbearia')
                                    ->schema([
                                        FileUpload::make('logo')
                                            ->label('Logo')
                                            ->image()
                                            ->directory('barbearias/logos')
                                            ->maxSize(2048)
                                            ->avatar()
                                            ->circleCropper()
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('1:1')
                                            ->imageResizeTargetWidth('500')
                                            ->imageResizeTargetHeight('500'),
                                    ]),
                                    
                                Section::make('Status')
                                    ->schema([
                                        Toggle::make('ativo')
                                            ->label('Barbearia ativa')
                                            ->default(true)
                                            ->required(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->nome) . '&color=FFFFFF&background=111827'),
                
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('telefone')
                    ->label('Telefone')
                    ->formatStateUsing(fn ($state) => formatarTelefone($state))
                    ->searchable(),
                
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                
                TextColumn::make('cidade')
                    ->label('Cidade')
                    ->sortable(),
                
                IconColumn::make('ativo')
                    ->label('Ativa')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('ativo')
                    ->label('Status')
                    ->options([
                        '1' => 'Ativas',
                        '0' => 'Inativas',
                    ]),
                Tables\Filters\Filter::make('cidade')
                    ->form([
                        TextInput::make('cidade')
                            ->label('Cidade'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['cidade'],
                            fn (Builder $query, $cidade): Builder => $query->where('cidade', 'like', "%{$cidade}%"),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Action::make('estatisticas')
                    ->label('Estatísticas')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn ($record) => route('filament.admin.resources.barbearias.estatisticas', $record))
                    ->hidden(fn ($record) => $record->trashed()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ClientesRelationManager::class,
            RelationManagers\ServicosRelationManager::class,
            RelationManagers\AgendamentosRelationManager::class,
            RelationManagers\BarbeirosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarbearias::route('/'),
            'create' => Pages\CreateBarbearia::route('/create'),
            'view' => Pages\ViewBarbearia::route('/{record}'),
            'edit' => Pages\EditBarbearia::route('/{record}/edit'),
            'estatisticas' => Pages\EstatisticasBarbearia::route('/{record}/estatisticas'),
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
        return static::getModel()::count();
    }
}