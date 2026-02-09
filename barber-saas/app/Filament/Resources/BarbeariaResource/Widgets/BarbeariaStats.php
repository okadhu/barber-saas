<?php

namespace App\Filament\Resources\BarbeariaResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Barbearia;
use App\Models\Cliente;
use App\Models\Agendamento;

class BarbeariaStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBarbearias = Barbearia::count();
        $barbeariasAtivas = Barbearia::where('ativo', true)->count();
        $totalClientes = Cliente::count();
        $agendamentosHoje = Agendamento::whereDate('data', today())->count();

        return [
            Stat::make('Total de Barbearias', $totalBarbearias)
                ->description($barbeariasAtivas . ' ativas')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('primary')
                ->chart([7, 10, 15, 12, 18, 20, 25]),

            Stat::make('Total de Clientes', $totalClientes)
                ->description('Clientes cadastrados')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart([20, 25, 30, 35, 40, 45, 50]),

            Stat::make('Agendamentos Hoje', $agendamentosHoje)
                ->description('Para hoje')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning')
                ->chart([5, 8, 10, 12, 15, 18, 20]),

            Stat::make('Taxa de Ocupação', $this->calcularTaxaOcupacao() . '%')
                ->description('Média de ocupação')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info')
                ->chart([60, 65, 70, 75, 80, 85, 90]),
        ];
    }

    protected function calcularTaxaOcupacao(): float
    {
        // Lógica simplificada para calcular taxa de ocupação
        $agendamentosConcluidos = Agendamento::where('status', 'concluido')
            ->where('created_at', '>=', now()->subMonth())
            ->count();
        
        $totalVagas = Barbearia::count() * 8 * 30; // 8 horas/dia * 30 dias
        
        if ($totalVagas == 0) {
            return 0;
        }
        
        return round(($agendamentosConcluidos / $totalVagas) * 100, 1);
    }
}