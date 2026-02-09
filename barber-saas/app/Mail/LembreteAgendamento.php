<?php

namespace App\Mail;

use App\Models\Agendamento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LembreteAgendamento extends Mailable
{
    use Queueable, SerializesModels;

    public $agendamento;

    public function __construct(Agendamento $agendamento)
    {
        $this->agendamento = $agendamento;
    }

    public function build()
    {
        return $this->subject('Lembrete de Agendamento - ' . $this->agendamento->barbearia->nome)
                    ->markdown('emails.lembrete-agendamento')
                    ->with([
                        'agendamento' => $this->agendamento,
                    ]);
    }
}