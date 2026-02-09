<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Barbearia;
use Carbon\Carbon;

class ValidarHorarioFuncionamento implements ValidationRule
{
    public function __construct(
        protected $barbeariaId,
        protected $data
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $barbearia = Barbearia::find($this->barbeariaId);
        
        if (!$barbearia) {
            $fail('Barbearia não encontrada.');
            return;
        }
        
        $diaSemana = Carbon::parse($this->data)->dayOfWeekIso; // 1=Segunda, 7=Domingo
        $hora = Carbon::parse($value);
        
        // Verifica se é sábado (6)
        if ($diaSemana == 6) {
            if (!$barbearia->horario_abertura_sabado || !$barbearia->horario_fechamento_sabado) {
                $fail('Horários de sábado não configurados.');
                return;
            }
            
            $abertura = Carbon::parse($barbearia->horario_abertura_sabado);
            $fechamento = Carbon::parse($barbearia->horario_fechamento_sabado);
            
            if ($hora->lt($abertura) || $hora->gte($fechamento)) {
                $aberturaFormatada = $abertura->format('H:i');
                $fechamentoFormatada = $fechamento->format('H:i');
                $fail("A barbearia funciona aos sábados das {$aberturaFormatada} às {$fechamentoFormatada}.");
                return;
            }
        }
        // Verifica se é domingo (7)
        elseif ($diaSemana == 7) {
            if (!$barbearia->abre_domingo) {
                $fail('A barbearia não abre aos domingos.');
                return;
            }
            
            if (!$barbearia->horario_abertura_domingo || !$barbearia->horario_fechamento_domingo) {
                $fail('Horários de domingo não configurados.');
                return;
            }
            
            $abertura = Carbon::parse($barbearia->horario_abertura_domingo);
            $fechamento = Carbon::parse($barbearia->horario_fechamento_domingo);
            
            if ($hora->lt($abertura) || $hora->gte($fechamento)) {
                $aberturaFormatada = $abertura->format('H:i');
                $fechamentoFormatada = $fechamento->format('H:i');
                $fail("A barbearia funciona aos domingos das {$aberturaFormatada} às {$fechamentoFormatada}.");
                return;
            }
        }
        // Segunda a sexta (1-5)
        else {
            $abertura = Carbon::parse($barbearia->horario_abertura_segunda_sex);
            $fechamento = Carbon::parse($barbearia->horario_fechamento_segunda_sex);
            
            if ($hora->lt($abertura) || $hora->gte($fechamento)) {
                $aberturaFormatada = $abertura->format('H:i');
                $fechamentoFormatada = $fechamento->format('H:i');
                $fail("A barbearia funciona de segunda a sexta das {$aberturaFormatada} às {$fechamentoFormatada}.");
                return;
            }
        }
        
        // Verifica se o horário é múltiplo do intervalo de agendamento
        $intervalo = $barbearia->tempo_intervalo_agendamento;
        $minutos = $hora->minute;
        
        if ($intervalo > 0 && $minutos % $intervalo != 0) {
            $fail("O horário deve ser em intervalos de {$intervalo} minutos.");
            return;
        }
        
        // Verifica se não está no passado (para agendamentos futuros)
        $dataHoraAgendamento = Carbon::parse($this->data . ' ' . $value);
        if ($dataHoraAgendamento->lt(now())) {
            $fail('Não é possível agendar para um horário no passado.');
            return;
        }
    }
}