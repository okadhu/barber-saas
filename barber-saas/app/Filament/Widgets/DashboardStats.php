<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Barbearia;
use App\Models\Cliente;
use App\Models\Agendamento;
use App\Models\Servico;
use Carbon\Carbon;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $hoje = Carbon::today();
        $mesAtual = Carbon::now()->format('Y-m');
        
        return [
            Stat::make('Total de Barbearias', Barbearia::count())
                ->description('Cadastradas no sistema')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('primary')
                ->chart($this->getBarbeariasChartData()),
            
            Stat::make('Clientes Ativos', Cliente::ativos()->count())
                ->description('Clientes cadastrados')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart($this->getClientesChartData()),
            
            Stat::make('Agendamentos Hoje', Agendamento::whereDate('data', $hoje)->count())
                ->description('Para hoje')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning')
                ->chart($this->getAgendamentosChartData()),
            
            Stat::make('Receita do Mês', 'R$ ' . number_format($this->getReceitaMes(), 2, ',', '.'))
                ->description('Arrecadado este mês')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('info')
                ->chart($this->getReceitaChartData()),
            
            Stat::make('Serviços Ativos', Servico::ativos()->count())
                ->description('Serviços disponíveis')
                ->descriptionIcon('heroicon-o-scissors')
                ->color('danger')
                ->chart($this->getServicosChartData()),
            
            Stat::make('Taxa de Ocupação', $this->getTaxaOcupacao() . '%')
                ->description('Média de ocupação')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('gray')
                ->chart($this->getOcupacaoChartData()),
        ];
    }

    protected function getReceitaMes(): float
    {
        return Agendamento::where('data', 'like', now()->format('Y-m') . '%')
            ->where('status', 'concluido')
            ->sum('valor');
    }

    protected function getTaxaOcupacao(): float
    {
        $agendamentosConcluidos = Agendamento::where('status', 'concluido')
            ->where('created_at', '>=', now()->subMonth())
            ->count();
        
        $barbearias = Barbearia::count();
        $totalVagas = $barbearias * 8 * 22; // 8 horas/dia * 22 dias úteis
        
        if ($totalVagas == 0) {
            return 0;
        }
        
        return round(($agendamentosConcluidos / $totalVagas) * 100, 1);
    }

    protected function getBarbeariasChartData(): array
    {
        return [5, 8, 12, 15, 18, 20, 25];
    }

    protected function getClientesChartData(): array
    {
        return [20, 35, 50, 65, 80, 95, 110];
    }

    protected function getAgendamentosChartData(): array
    {
        return [10, 15, 12, 18, 20, 25, 30];
    }

    protected function getReceitaChartData(): array
    {
        return [1500, 2000, 2500, 3000, 3500, 4000, 4500];
    }

    protected function getServicosChartData(): array
    {
        return [6, 8, 10, 12, 14, 16, 18];
    }

    protected function getOcupacaoChartData(): array
    {
        return [60, 65, 70, 75, 80, 85, 90];
    }
}