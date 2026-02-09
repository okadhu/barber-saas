<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Barbearia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'slug',
        'telefone',
        'email',
        'cnpj',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'logo',
        'horario_abertura_segunda_sex',
        'horario_fechamento_segunda_sex',
        'horario_abertura_sabado',
        'horario_fechamento_sabado',
        'abre_domingo',
        'horario_abertura_domingo',
        'horario_fechamento_domingo',
        'tempo_intervalo_agendamento',
        'ativo'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($barbearia) {
            $barbearia->slug = Str::slug($barbearia->nome) . '-' . Str::random(6);
        });

        static::updating(function ($barbearia) {
            if ($barbearia->isDirty('nome')) {
                $barbearia->slug = Str::slug($barbearia->nome) . '-' . Str::random(6);
            }
        });
    }

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
                    ->withPivot('papel', 'ativo')
                    ->wherePivot('ativo', true)
                    ->withTimestamps();
    }

    public function getEnderecoCompletoAttribute()
    {
        return sprintf(
            '%s, %s - %s, %s - %s, %s',
            $this->endereco,
            $this->numero,
            $this->bairro,
            $this->cidade,
            $this->estado,
            formatarCEP($this->cep)
        );
    }

    public function getTelefoneFormatadoAttribute()
    {
        return formatarTelefone($this->telefone);
    }

    public function getCnpjFormatadoAttribute()
    {
        return formatarCNPJ($this->cnpj);
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function estaAberta($data, $hora)
    {
        return verificarHorarioFuncionamento($this, $data, $hora);
    }
}