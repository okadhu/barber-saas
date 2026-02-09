<?php

namespace App\Filament\Resources\BarbeariaResource\Pages;

use App\Filament\Resources\BarbeariaResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Widgets\StatsOverviewWidget;
use App\Models\Barbearia;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class EstatisticasBarbearia extends Page
{
    protected static string $resource = BarbeariaResource::class;

    protected static string $view = 'filament.resources.barbearia-resource.pages.estatisticas-barbearia';

    public $record;
    public $estatisticas = [];

    public function mount($record): void
    {
        $this->record = Barbearia::find($record);
        $this->loadEstatisticas();
    }

    protected function loadEstatisticas(): void
    {
        $barbeariaId = $this->record->id;
        $mesAtual = now()->format('Y-m');
        $semanaPassada = now()->subWeek();
        $mesPassado = now()->subMonth();

        // Estatísticas básicas
        $this->estatisticas = [
            'clientes' => Cliente::daBarbearia($barbeariaId)->ativos()->count(),
            'servicos' => Servico::daBarbearia($barbeariaId)->ativos()->count(),
            'barbeiros' => $this->record->barbeiros()->count(),
            'agendamentos_mes' => Agendamento::daBarbearia($barbeariaId)
                ->where('data', 'like', "{$mesAtual}%")
                ->count(),
            'agendamentos_semana' => Agendamento::daBarbearia($barbeariaId)
                ->whereBetween('data', [$semanaPassada, now()])
                ->count(),
            'receita_mes' => Agendamento::daBarbearia($barbeariaId)
                ->where('data', 'like', "{$mesAtual}%")
                ->where('status', 'concluido')
                ->sum('valor'),
            'receita_mes_passado' => Agendamento::daBarbearia($barbeariaId)
                ->whereBetween('data', [$mesPassado, now()->subMonth()->endOfMonth()])
                ->where('status', 'concluido')
                ->sum('valor'),
            'taxa_confirmacao' => $this->calcularTaxaConfirmacao($barbeariaId),
            'servico_mais_popular' => $this->obterServicoMaisPopular($barbeariaId),
            'barbeiro_mais_ocupado' => $this->obterBarbeiroMaisOcupado($barbeariaId),
        ];
    }

    protected function calcularTaxaConfirmacao($barbeariaId): float
    {
        $totalAgendamentos = Agendamento::daBarbearia($barbeariaId)
            ->where('created_at', '>=', now()->subMonth())
            ->count();
        
        $agendamentosConcluidos = Agendamento::daBarbearia($barbeariaId)
            ->where('created_at', '>=', now()->subMonth())
            ->where('status', 'concluido')
            ->count();
        
        if ($totalAgendamentos == 0) {
            return 0;
        }
        
        return round(($agendamentosConcluidos / $totalAgendamentos) * 100, 2);
    }

    protected function obterServicoMaisPopular($barbeariaId): ?string
    {
        $servico = Servico::daBarbearia($barbeariaId)
            ->withCount(['agendamentos' => function ($query) {
                $query->where('created_at', '>=', now()->subMonth());
            }])
            ->orderBy('agendamentos_count', 'desc')
            ->first();
        
        return $servico ? $servico->nome . ' (' . $servico->agendamentos_count . ' agendamentos)' : 'Nenhum';
    }

    protected function obterBarbeiroMaisOcupado($barbeariaId): ?string
    {
        $barbeiro = $this->record->barbeiros()
            ->withCount(['agendamentos' => function ($query) use ($barbeariaId) {
                $query->where('barbearia_id', $barbeariaId)
                    ->where('created_at', '>=', now()->subMonth());
            }])
            ->orderBy('agendamentos_count', 'desc')
            ->first();
        
        return $barbeiro ? $barbeiro->name . ' (' . $barbeiro->agendamentos_count . ' agendamentos)' : 'Nenhum';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voltar')
                ->label('Voltar')
                ->url(BarbeariaResource::getUrl('view', ['record' => $this->record]))
                ->color('gray'),
            Actions\Action::make('exportar')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success'),
        ];
    }
}