<?php

namespace App\Filament\Resources\AgendamentoResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Agendamento;
use Carbon\Carbon;

class AgendamentoStats extends BaseWidget
{
    protected function getStats(): array
    {
        $hoje = Carbon::today();
        $amanha = Carbon::tomorrow();
        $estaSemanaInicio = Carbon::now()->startOfWeek();
        $estaSemanaFim = Carbon::now()->endOfWeek();

        return [
            Stat::make('Agendamentos Hoje', Agendamento::whereDate('data', $hoje)->count())
                ->description('Para hoje')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success')
                ->chart($this->getChartData('today')),

            Stat::make('Agendamentos Amanhã', Agendamento::whereDate('data', $amanha)->count())
                ->description('Para amanhã')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning')
                ->chart($this->getChartData('tomorrow')),

            Stat::make('Esta Semana', Agendamento::whereBetween('data', [$estaSemanaInicio, $estaSemanaFim])->count())
                ->description('Semana atual')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info')
                ->chart($this->getChartData('week')),

            Stat::make('Pendentes', Agendamento::where('status', 'pendente')->count())
                ->description('Aguardando confirmação')
                ->descriptionIcon('heroicon-o-clock')
                ->color('danger'),
        ];
    }

    protected function getChartData($type): array
    {
        // Dados de exemplo para os gráficos
        switch ($type) {
            case 'today':
                return [12, 15, 18, 16, 14, 20, 22];
            case 'tomorrow':
                return [8, 10, 12, 14, 16, 18, 20];
            case 'week':
                return [40, 45, 50, 55, 60, 65, 70];
            default:
                return [0, 0, 0, 0, 0, 0, 0];
        }
    }
}