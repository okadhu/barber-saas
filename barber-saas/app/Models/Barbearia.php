<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\FormatHelper; // Adicione esta linha

class Barbearia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'cnpj',
        'telefone',
        'email',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'ativo',
        'horario_abertura_segunda_sex',
        'horario_fechamento_segunda_sex',
        'horario_abertura_sabado',
        'horario_fechamento_sabado',
        'horario_abertura_domingo',
        'horario_fechamento_domingo',
        'abre_domingo',
        'tempo_intervalo_agendamento',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'abre_domingo' => 'boolean',
    ];

    protected $appends = [
        'endereco_completo',
        'telefone_formatado',
        'cnpj_formatado',
    ];

    // Relacionamentos
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function servicos()
    {
        return $this->hasMany(Servico::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    public function barbeiros()
    {
        return $this->belongsToMany(User::class, 'barbearia_user')
                    ->withPivot('ativo')
                    ->withTimestamps()
                    ->select('users.id', 'users.name', 'users.email'); // Adicione esta linha
    }

    // Accessors
    public function getEnderecoCompletoAttribute()
    {
        $endereco = array_filter([
            $this->endereco,
            $this->numero,
            $this->bairro,
            $this->cidade,
            $this->estado,
            FormatHelper::formatarCEP($this->cep) // Corrigido aqui
        ]);

        return implode(', ', $endereco);
    }

    public function getTelefoneFormatadoAttribute()
    {
        return FormatHelper::formatarTelefone($this->telefone); // Corrigido aqui
    }

    public function getCnpjFormatadoAttribute()
    {
        return FormatHelper::formatarCNPJ($this->cnpj); // Corrigido aqui
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorCidade($query, $cidade)
    {
        return $query->where('cidade', $cidade);
    }
}