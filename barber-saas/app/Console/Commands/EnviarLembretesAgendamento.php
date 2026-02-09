<?php

namespace App\Console\Commands;

use App\Models\Agendamento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\LembreteAgendamento;
use Carbon\Carbon;

class EnviarLembretesAgendamento extends Command
{
    protected $signature = 'agendamentos:lembrar';
    protected $description = 'Envia lembretes de agendamentos para o próximo dia';

    public function handle()
    {
        $amanha = Carbon::tomorrow()->format('Y-m-d');
        
        $agendamentos = Agendamento::with(['cliente', 'servico', 'barbearia'])
            ->whereDate('data', $amanha)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->get();
        
        $enviados = 0;
        
        foreach ($agendamentos as $agendamento) {
            if ($agendamento->cliente->email) {
                try {
                    Mail::to($agendamento->cliente->email)
                        ->send(new LembreteAgendamento($agendamento));
                    
                    $enviados++;
                    $this->info("Lembrete enviado para: {$agendamento->cliente->email}");
                } catch (\Exception $e) {
                    $this->error("Erro ao enviar para {$agendamento->cliente->email}: " . $e->getMessage());
                }
            }
        }
        
        $this->info("Total de lembretes enviados: {$enviados}");
        
        return Command::SUCCESS;
    }
}