<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barbearia_id',
        'nome',
        'telefone',
        'email',
        'data_nascimento',
        'foto',
        'observacoes',
        'ativo'
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function barbearia()
    {
        return $this->belongsTo(Barbearia::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    public function getIdadeAttribute()
    {
        if (!$this->data_nascimento) {
            return null;
        }
        
        return now()->diffInYears($this->data_nascimento);
    }

    public function getTelefoneFormatadoAttribute()
    {
        return formatarTelefone($this->telefone);
    }

    public function getIniciaisAttribute()
    {
        $nomes = explode(' ', $this->nome);
        $iniciais = '';
        
        if (count($nomes) >= 2) {
            $iniciais = strtoupper(substr($nomes[0], 0, 1) . substr($nomes[count($nomes) - 1], 0, 1));
        } else {
            $iniciais = strtoupper(substr($this->nome, 0, 2));
        }
        
        return $iniciais;
    }

    public function getTotalAgendamentosAttribute()
    {
        return $this->agendamentos()->count();
    }

    public function getTotalAgendamentosConcluidosAttribute()
    {
        return $this->agendamentos()->where('status', 'concluido')->count();
    }

    public function getTotalGastoAttribute()
    {
        return $this->agendamentos()->where('status', 'concluido')->sum('valor');
    }

    public function scopeDaBarbearia($query, $barbeariaId)
    {
        return $query->where('barbearia_id', $barbeariaId);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorNomeOuTelefone($query, $search)
    {
        return $query->where('nome', 'like', "%{$search}%")
                     ->orWhere('telefone', 'like', "%{$search}%");
    }
}