<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicoResource\Pages;
use App\Models\Servico;
use App\Models\Barbearia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ServicoResource extends Resource
{
    protected static ?string $model = Servico::class;

    protected static ?string $navigationIcon = 'heroicon-o-scissors';

    protected static ?string $navigationGroup = 'Gestão';

    protected static ?string $navigationLabel = 'Serviços';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Serviço')
                    ->schema([
                        Select::make('barbearia_id')
                            ->label('Barbearia')
                            ->options(Barbearia::ativas()->pluck('nome', 'id'))
                            ->required()
                            ->searchable(),
                        
                        TextInput::make('nome')
                            ->label('Nome do Serviço')
                            ->required()
                            ->maxLength(255),
                        
                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->rows(3)
                            ->maxLength(500),
                        
                        TextInput::make('preco')
                            ->label('Preço (R$)')
                            ->numeric()
                            ->required()
                            ->prefix('R$')
                            ->minValue(0)
                            ->step(0.01),
                        
                        TextInput::make('duracao')
                            ->label('Duração (minutos)')
                            ->numeric()
                            ->required()
                            ->suffix('minutos')
                            ->minValue(5)
                            ->maxValue(240),
                        
                        TextInput::make('ordem')
                            ->label('Ordem de exibição')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Imagem e Status')
                    ->schema([
                        FileUpload::make('imagem')
                            ->label('Imagem do Serviço')
                            ->image()
                            ->directory('servicos/imagens')
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('450'),
                        
                        Toggle::make('ativo')
                            ->label('Serviço ativo')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagem')
                    ->label('Imagem')
                    ->square()
                    ->size(50)
                    ->defaultImageUrl('https://via.placeholder.com/50x50?text=S'),
                
                TextColumn::make('nome')
                    ->label('Serviço')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('barbearia.nome')
                    ->label('Barbearia')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('preco')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                
                TextColumn::make('duracao_formatada')
                    ->label('Duração')
                    ->sortable(['duracao']),
                
                TextColumn::make('total_agendamentos')
                    ->label('Agendamentos')
                    ->sortable()
                    ->toggleable(),
                
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('barbearia_id')
                    ->label('Barbearia')
                    ->options(Barbearia::ativas()->pluck('nome', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Ativo'),
                Tables\Filters\Filter::make('preco_min')
                    ->form([
                        TextInput::make('preco_min')
                            ->label('Preço mínimo')
                            ->numeric()
                            ->prefix('R$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['preco_min'],
                            fn (Builder $query, $preco): Builder => $query->where('preco', '>=', $preco),
                        );
                    }),
                Tables\Filters\Filter::make('preco_max')
                    ->form([
                        TextInput::make('preco_max')
                            ->label('Preço máximo')
                            ->numeric()
                            ->prefix('R$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['preco_max'],
                            fn (Builder $query, $preco): Builder => $query->where('preco', '<=', $preco),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServicos::route('/'),
            'create' => Pages\CreateServico::route('/create'),
            'view' => Pages\ViewServico::route('/{record}'),
            'edit' => Pages\EditServico::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}