<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servico extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barbearia_id',
        'nome',
        'descricao',
        'preco',
        'duracao',
        'imagem',
        'ativo',
        'ordem'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
    ];

    public function barbearia()
    {
        return $this->belongsTo(Barbearia::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    public function getPrecoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco, 2, ',', '.');
    }

    public function getDuracaoFormatadaAttribute()
    {
        $horas = floor($this->duracao / 60);
        $minutos = $this->duracao % 60;
        
        if ($horas > 0) {
            return "{$horas}h" . ($minutos > 0 ? " {$minutos}min" : "");
        }
        
        return "{$minutos} min";
    }

    public function scopeDaBarbearia($query, $barbeariaId)
    {
        return $query->where('barbearia_id', $barbeariaId);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    public function getTotalAgendamentosAttribute()
    {
        return $this->agendamentos()->count();
    }

    public function getReceitaTotalAttribute()
    {
        return $this->agendamentos()->where('status', 'concluido')->sum('valor');
    }
}