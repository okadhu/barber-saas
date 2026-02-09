<?php

namespace App\Console\Commands;

use App\Models\Agendamento;
use App\Models\Barbearia;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GerarRelatorioDiario extends Command
{
    protected $signature = 'relatorio:diario {--data= : Data do relatório (formato: Y-m-d)}';
    protected $description = 'Gera relatório diário de agendamentos';

    public function handle()
    {
        $data = $this->option('data') ? Carbon::parse($this->option('data')) : Carbon::today();
        $dataFormatada = $data->format('Y-m-d');
        
        $this->info("Gerando relatório para: {$dataFormatada}");
        
        $agendamentos = Agendamento::with(['cliente', 'servico', 'barbeiro', 'barbearia'])
            ->whereDate('data', $dataFormatada)
            ->orderBy('hora_inicio')
            ->get();
        
        $barbearias = Barbearia::withCount(['agendamentos' => function ($query) use ($dataFormatada) {
            $query->whereDate('data', $dataFormatada);
        }])->get();
        
        // Criar conteúdo do relatório
        $conteudo = "RELATÓRIO DIÁRIO - {$data->format('d/m/Y')}\n";
        $conteudo .= str_repeat("=", 50) . "\n\n";
        
        // Estatísticas gerais
        $conteudo .= "ESTATÍSTICAS GERAIS:\n";
        $conteudo .= "Total de agendamentos: " . $agendamentos->count() . "\n";
        $conteudo .= "Agendamentos confirmados: " . $agendamentos->where('status', 'confirmado')->count() . "\n";
        $conteudo .= "Agendamentos concluídos: " . $agendamentos->where('status', 'concluido')->count() . "\n";
        $conteudo .= "Receita total: R$ " . number_format($agendamentos->where('status', 'concluido')->sum('valor'), 2, ',', '.') . "\n\n";
        
        // Por barbearia
        $conteudo .= "POR BARBEARIA:\n";
        foreach ($barbearias as $barbearia) {
            $agendamentosBarbearia = $agendamentos->where('barbearia_id', $barbearia->id);
            $receita = $agendamentosBarbearia->where('status', 'concluido')->sum('valor');
            
            $conteudo .= "  {$barbearia->nome}:\n";
            $conteudo .= "    Agendamentos: {$barbearia->agendamentos_count}\n";
            $conteudo .= "    Receita: R$ " . number_format($receita, 2, ',', '.') . "\n";
        }
        
        $conteudo .= "\nDETALHES DOS AGENDAMENTOS:\n";
        $conteudo .= str_repeat("-", 100) . "\n";
        
        foreach ($agendamentos as $agendamento) {
            $conteudo .= sprintf(
                "%s | %s | %s | %s | %s | R$ %s | %s\n",
                $agendamento->hora_inicio->format('H:i'),
                $agendamento->cliente->nome,
                $agendamento->servico->nome,
                $agendamento->barbeiro->name,
                $agendamento->barbearia->nome,
                number_format($agendamento->valor, 2, ',', '.'),
                getStatusLabel($agendamento->status)
            );
        }
        
        // Salvar arquivo
        $nomeArquivo = "relatorio_diario_{$dataFormatada}.txt";
        Storage::disk('local')->put("relatorios/{$nomeArquivo}", $conteudo);
        
        $this->info("Relatório salvo em: storage/app/relatorios/{$nomeArquivo}");
        
        return Command::SUCCESS;
    }
}