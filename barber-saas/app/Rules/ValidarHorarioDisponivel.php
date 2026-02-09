<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Agendamento;

class ValidarHorarioDisponivel implements ValidationRule
{
    public function __construct(
        protected $barbeariaId,
        protected $data,
        protected $duracao,
        protected $barbeiroId = null,
        protected $agendamentoId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $horaInicio = $value;
        $horaFim = date('H:i', strtotime("+{$this->duracao} minutes", strtotime($horaInicio)));
        
        $query = Agendamento::where('barbearia_id', $this->barbeariaId)
            ->where('data', $this->data)
            ->where(function ($q) use ($horaInicio, $horaFim) {
                $q->where(function ($q) use ($horaInicio, $horaFim) {
                    $q->where('hora_inicio', '<', $horaFim)
                      ->where('hora_fim', '>', $horaInicio);
                });
            })
            ->whereNotIn('status', ['cancelado', 'nao_compareceu']);
        
        if ($this->barbeiroId) {
            $query->where('barbeiro_id', $this->barbeiroId);
        }
        
        if ($this->agendamentoId) {
            $query->where('id', '!=', $this->agendamentoId);
        }
        
        if ($query->exists()) {
            $fail('Este horário já está ocupado. Por favor, escolha outro horário.');
        }
    }
}