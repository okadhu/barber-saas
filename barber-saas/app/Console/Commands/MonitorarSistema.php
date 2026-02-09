<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MonitorarSistema extends Command
{
    protected $signature = 'sistema:monitorar';
    protected $description = 'Monitora a saúde do sistema e gera relatórios';

    public function handle()
    {
        $relatorio = [];
        
        // 1. Verificar conexão com banco de dados
        try {
            DB::connection()->getPdo();
            $relatorio['banco_dados'] = [
                'status' => 'OK',
                'mensagem' => 'Conexão estabelecida com sucesso'
            ];
        } catch (\Exception $e) {
            $relatorio['banco_dados'] = [
                'status' => 'ERRO',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // 2. Verificar espaço em disco
        $discoTotal = disk_total_space('/');
        $discoLivre = disk_free_space('/');
        $discoUsado = $discoTotal - $discoLivre;
        $percentualUsado = ($discoUsado / $discoTotal) * 100;
        
        $relatorio['disco'] = [
            'total' => $this->formatarBytes($discoTotal),
            'livre' => $this->formatarBytes($discoLivre),
            'usado' => $this->formatarBytes($discoUsado),
            'percentual_usado' => round($percentualUsado, 2) . '%'
        ];
        
        // 3. Verificar logs de erro
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logSize = filesize($logFile);
            $relatorio['logs'] = [
                'tamanho' => $this->formatarBytes($logSize),
                'arquivo' => $logFile
            ];
        }
        
        // 4. Verificar agendamentos atrasados
        $agendamentosAtrasados = \App\Models\Agendamento::where('status', 'em_atendimento')
            ->where('data', '<', now()->format('Y-m-d'))
            ->orWhere(function ($query) {
                $query->where('data', now()->format('Y-m-d'))
                      ->where('hora_fim', '<', now()->format('H:i:s'))
                      ->where('status', 'em_atendimento');
            })
            ->count();
        
        $relatorio['agendamentos'] = [
            'atrasados' => $agendamentosAtrasados
        ];
        
        // 5. Salvar relatório
        $nomeArquivo = 'relatorio_monitoramento_' . now()->format('Y-m-d_H-i-s') . '.json';
        Storage::disk('local')->put('monitoramento/' . $nomeArquivo, json_encode($relatorio, JSON_PRETTY_PRINT));
        
        $this->info("Relatório de monitoramento gerado: " . $nomeArquivo);
        
        // 6. Enviar alertas se necessário
        if ($percentualUsado > 90) {
            $this->alert("⚠️  ALERTA: Espaço em disco quase esgotado ({$relatorio['disco']['percentual_usado']})");
        }
        
        if ($agendamentosAtrasados > 0) {
            $this->alert("⚠️  ALERTA: $agendamentosAtrasados agendamento(s) em atraso");
        }
        
        return Command::SUCCESS;
    }
    
    private function formatarBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}