<?php

namespace App\Filament\Resources\BarbeariaResource\Pages;

use App\Filament\Resources\BarbeariaResource;
use App\Models\Agendamento;
use App\Models\Barbearia;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\Page as ResourcePage; // Adicione esta linha

class EstatisticasBarbearia extends ResourcePage // Mude para ResourcePage
{
    protected static string $resource = BarbeariaResource::class;

    protected static string $view = 'filament.resources.barbearia-resource.pages.estatisticas-barbearia';

    public Barbearia $record;

    public $periodo = 'mes';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('voltar')
                ->label('Voltar')
                ->url(fn () => BarbeariaResource::getUrl('view', ['record' => $this->record]))
                ->color('gray'),
        ];
    }

    public function mount($record): void
    {
        $this->record = Barbearia::findOrFail($record);
    }

    public function getStats(): array
    {
        $stats = [];

        // Agendamentos totais
        $totalAgendamentos = Agendamento::where('barbearia_id', $this->record->id)->count();
        $stats[] = [
            'label' => 'Agendamentos Totais',
            'value' => $totalAgendamentos,
            'description' => 'Total de agendamentos',
            'color' => 'primary'
        ];

        // Agendamentos este mês
        $agendamentosMes = Agendamento::where('barbearia_id', $this->record->id)
            ->whereMonth('data', now()->month)
            ->whereYear('data', now()->year)
            ->count();
        $stats[] = [
            'label' => 'Este Mês',
            'value' => $agendamentosMes,
            'description' => 'Agendamentos no mês atual',
            'color' => 'success'
        ];

        // Taxa de conclusão
        $agendamentosConcluidos = Agendamento::where('barbearia_id', $this->record->id)
            ->where('status', 'concluido')
            ->count();
        $taxaConclusao = $totalAgendamentos > 0 ? round(($agendamentosConcluidos / $totalAgendamentos) * 100, 1) : 0;
        $stats[] = [
            'label' => 'Taxa de Conclusão',
            'value' => $taxaConclusao . '%',
            'description' => 'Percentual de serviços concluídos',
            'color' => $taxaConclusao >= 80 ? 'success' : ($taxaConclusao >= 60 ? 'warning' : 'danger')
        ];

        // Faturamento total
        $faturamentoTotal = Agendamento::where('barbearia_id', $this->record->id)
            ->where('status', 'concluido')
            ->sum('valor');
        $stats[] = [
            'label' => 'Faturamento Total',
            'value' => 'R$ ' . number_format($faturamentoTotal, 2, ',', '.'),
            'description' => 'Faturamento com serviços concluídos',
            'color' => 'success'
        ];

        // Faturamento este mês
        $faturamentoMes = Agendamento::where('barbearia_id', $this->record->id)
            ->where('status', 'concluido')
            ->whereMonth('data', now()->month)
            ->whereYear('data', now()->year)
            ->sum('valor');
        $stats[] = [
            'label' => 'Faturamento Mês',
            'value' => 'R$ ' . number_format($faturamentoMes, 2, ',', '.'),
            'description' => 'Faturamento no mês atual',
            'color' => 'info'
        ];

        // Clientes ativos
        $clientesAtivos = $this->record->clientes()->where('ativo', true)->count();
        $stats[] = [
            'label' => 'Clientes Ativos',
            'value' => $clientesAtivos,
            'description' => 'Clientes ativos na barbearia',
            'color' => 'primary'
        ];

        return $stats;
    }

    public function getAgendamentosPorStatus(): array
    {
        $statusCounts = Agendamento::where('barbearia_id', $this->record->id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusLabels = [
            'pendente' => 'Pendente',
            'confirmado' => 'Confirmado',
            'em_atendimento' => 'Em Atendimento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'nao_compareceu' => 'Não Compareceu',
        ];

        $data = [];
        $colors = [
            'pendente' => '#f59e0b',
            'confirmado' => '#3b82f6',
            'em_atendimento' => '#8b5cf6',
            'concluido' => '#10b981',
            'cancelado' => '#ef4444',
            'nao_compareceu' => '#6b7280',
        ];

        foreach ($statusLabels as $key => $label) {
            $data[] = [
                'status' => $label,
                'total' => $statusCounts[$key] ?? 0,
                'color' => $colors[$key] ?? '#6b7280',
            ];
        }

        return $data;
    }

    public function getTopServicos(): array
    {
        return Agendamento::where('barbearia_id', $this->record->id)
            ->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->select('servicos.nome', DB::raw('count(*) as total'))
            ->groupBy('servicos.id', 'servicos.nome')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'servico' => $item->nome,
                'total' => $item->total,
            ])
            ->toArray();
    }

    public function getAgendamentosPorMes(): array
    {
        $agendamentos = Agendamento::where('barbearia_id', $this->record->id)
            ->whereYear('data', now()->year)
            ->select(DB::raw('MONTH(data) as mes'), DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $meses = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $agendamento = $agendamentos->firstWhere('mes', $i);
            $data[] = [
                'mes' => $meses[$i],
                'total' => $agendamento ? $agendamento->total : 0,
            ];
        }

        return $data;
    }

    protected function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
            'agendamentosPorStatus' => $this->getAgendamentosPorStatus(),
            'topServicos' => $this->getTopServicos(),
            'agendamentosPorMes' => $this->getAgendamentosPorMes(),
            'barbearia' => $this->record,
        ];
    }
}