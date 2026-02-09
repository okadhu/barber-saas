@component('mail::message')
# Lembrete de Agendamento

Olá {{ $agendamento->cliente->nome }},

Este é um lembrete do seu agendamento na **{{ $agendamento->barbearia->nome }}**.

**Detalhes do Agendamento:**
- **Data:** {{ $agendamento->data->format('d/m/Y') }}
- **Horário:** {{ $agendamento->hora_inicio->format('H:i') }}
- **Serviço:** {{ $agendamento->servico->nome }}
- **Barbeiro:** {{ $agendamento->barbeiro->name }}
- **Valor:** R$ {{ number_format($agendamento->valor, 2, ',', '.') }}

**Endereço:**  
{{ $agendamento->barbearia->endereco }}, {{ $agendamento->barbearia->numero }}  
{{ $agendamento->barbearia->bairro }} - {{ $agendamento->barbearia->cidade }}/{{ $agendamento->barbearia->estado }}  
CEP: {{ formatarCEP($agendamento->barbearia->cep) }}

**Telefone:** {{ formatarTelefone($agendamento->barbearia->telefone) }}

@component('mail::button', ['url' => config('app.url') . '/admin', 'color' => 'success'])
Acessar Sistema
@endcomponent

**Observações:**  
Por favor, chegue com 10 minutos de antecedência.  
Em caso de cancelamento, entre em contato com pelo menos 2 horas de antecedência.

Atenciosamente,  
Equipe {{ $agendamento->barbearia->nome }}
@endcomponent