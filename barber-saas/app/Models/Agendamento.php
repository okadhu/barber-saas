<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Agendamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barbearia_id',
        'cliente_id',
        'servico_id',
        'barbeiro_id',
        'data',
        'hora_inicio',
        'hora_fim',
        'status',
        'valor',
        'observacoes',
        'confirmado_em',
        'cancelado_em',
        'motivo_cancelamento'
    ];

    protected $casts = [
        'data' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fim' => 'datetime:H:i',
        'valor' => 'decimal:2',
        'confirmado_em' => 'datetime',
        'cancelado_em' => 'datetime',
    ];

    public function barbearia()
    {
        return $this->belongsTo(Barbearia::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function barbeiro()
    {
        return $this->belongsTo(User::class, 'barbeiro_id');
    }

    public function getDataFormatadaAttribute()
    {
        return $this->data->format('d/m/Y');
    }

    public function getHoraInicioFormatadaAttribute()
    {
        return Carbon::parse($this->hora_inicio)->format('H:i');
    }

    public function getHoraFimFormatadaAttribute()
    {
        return Carbon::parse($this->hora_fim)->format('H:i');
    }

    public function getDataHoraInicioAttribute()
    {
        return Carbon::parse($this->data->format('Y-m-d') . ' ' . $this->hora_inicio);
    }

    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function getStatusLabelAttribute()
    {
        return getStatusLabel($this->status);
    }

    public function getStatusColorAttribute()
    {
        return getStatusColor($this->status);
    }

    // Scopes
    public function scopeHoje($query)
    {
        return $query->whereDate('data', today());
    }

    public function scopeFuturos($query)
    {
        return $query->whereDate('data', '>=', today());
    }

    public function scopePassados($query)
    {
        return $query->whereDate('data', '<', today());
    }

    public function scopeDaBarbearia($query, $barbeariaId)
    {
        return $query->where('barbearia_id', $barbeariaId);
    }

    public function scopeDoBarbeiro($query, $barbeiroId)
    {
        return $query->where('barbeiro_id', $barbeiroId);
    }

    public function scopeDoCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeConfirmados($query)
    {
        return $query->where('status', 'confirmado');
    }

    public function scopeConcluidos($query)
    {
        return $query->where('status', 'concluido');
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    // Métodos de ação
    public function confirmar()
    {
        $this->update([
            'status' => 'confirmado',
            'confirmado_em' => now()
        ]);
    }

    public function cancelar($motivo = null)
    {
        $this->update([
            'status' => 'cancelado',
            'cancelado_em' => now(),
            'motivo_cancelamento' => $motivo
        ]);
    }

    public function iniciar()
    {
        $this->update(['status' => 'em_atendimento']);
    }

    public function concluir()
    {
        $this->update(['status' => 'concluido']);
    }

    public function marcarComoNaoCompareceu()
    {
        $this->update(['status' => 'nao_compareceu']);
    }

    // Verificações de status
    public function estaConfirmado()
    {
        return $this->status === 'confirmado';
    }

    public function estaConcluido()
    {
        return $this->status === 'concluido';
    }

    public function estaCancelado()
    {
        return $this->status === 'cancelado';
    }

    public function estaPendente()
    {
        return $this->status === 'pendente';
    }
}