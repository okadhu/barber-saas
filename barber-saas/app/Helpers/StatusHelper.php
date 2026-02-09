<?php

namespace App\Helpers;

class StatusHelper
{
    public static function getStatusLabel($status): string
    {
        return match($status) {
            'agendado' => 'Agendado',
            'confirmado' => 'Confirmado',
            'em_andamento' => 'Em Andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'nao_compareceu' => 'Não Compareceu',
            default => $status,
        };
    }

    public static function getStatusColor($status): string
    {
        return match($status) {
            'agendado' => 'warning',
            'confirmado' => 'info',
            'em_andamento' => 'primary',
            'concluido' => 'success',
            'cancelado' => 'danger',
            'nao_compareceu' => 'gray',
            default => 'secondary',
        };
    }
}