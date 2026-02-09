<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relacionamento com barbearias (pivot)
     */
    public function barbearias()
    {
        return $this->belongsToMany(Barbearia::class, 'barbearia_user')
                    ->withPivot('papel', 'ativo')
                    ->withTimestamps();
    }

    /**
     * Verifica se o usuário é admin de alguma barbearia
     */
    public function isBarbeariaAdmin()
    {
        return $this->barbearias()
            ->wherePivot('papel', 'admin')
            ->exists();
    }

    /**
     * Verifica se o usuário é barbeiro de alguma barbearia
     */
    public function isBarbeiro()
    {
        return $this->barbearias()
            ->wherePivot('papel', 'barbeiro')
            ->wherePivot('ativo', true)
            ->exists();
    }

    /**
     * Verifica se o usuário é recepcionista de alguma barbearia
     */
    public function isRecepcionista()
    {
        return $this->barbearias()
            ->wherePivot('papel', 'recepcionista')
            ->wherePivot('ativo', true)
            ->exists();
    }

    /**
     * Obtém o papel do usuário em uma barbearia específica
     */
    public function getPapelNaBarbearia($barbeariaId)
    {
        $barbearia = $this->barbearias()
            ->where('barbearia_id', $barbeariaId)
            ->first();
        
        return $barbearia ? $barbearia->pivot->papel : null;
    }

    /**
     * Obtém todas as barbearias onde o usuário está ativo
     */
    public function barbeariasAtivas()
    {
        return $this->barbearias()
            ->wherePivot('ativo', true);
    }

    /**
     * Agendamentos do barbeiro
     */
    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'barbeiro_id');
    }

    /**
     * Agendamentos futuros do barbeiro
     */
    public function agendamentosFuturos()
    {
        return $this->agendamentos()->futuros();
    }

    /**
     * Agendamentos de hoje do barbeiro
     */
    public function agendamentosHoje()
    {
        return $this->agendamentos()->hoje();
    }
}