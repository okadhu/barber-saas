<?php

namespace App\Helpers;

class FormatHelper
{
    public static function formatarTelefone($telefone): string
    {
        if (empty($telefone)) {
            return '-';
        }
        
        // Remove caracteres não numéricos
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        // Formatação para telefones brasileiros
        $length = strlen($telefone);
        
        if ($length === 11) { // Celular com DDD (11 91234-5678)
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 5) . '-' . 
                   substr($telefone, 7);
        } elseif ($length === 10) { // Fixo com DDD (11 1234-5678)
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 4) . '-' . 
                   substr($telefone, 6);
        } elseif ($length === 9) { // Celular sem DDD (91234-5678)
            return substr($telefone, 0, 5) . '-' . 
                   substr($telefone, 5);
        } elseif ($length === 8) { // Fixo sem DDD (1234-5678)
            return substr($telefone, 0, 4) . '-' . 
                   substr($telefone, 4);
        }
        
        return $telefone; // Retorna original se não corresponder
    }
    
    public static function formatarCPF($cpf): string
    {
        if (empty($cpf)) {
            return '-';
        }
        
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . 
                   substr($cpf, 3, 3) . '.' . 
                   substr($cpf, 6, 3) . '-' . 
                   substr($cpf, 9);
        }
        
        return $cpf;
    }
    
    public static function formatarCNPJ($cnpj): string
    {
        if (empty($cnpj)) {
            return '-';
        }
        
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) === 14) {
            return substr($cnpj, 0, 2) . '.' . 
                   substr($cnpj, 2, 3) . '.' . 
                   substr($cnpj, 5, 3) . '/' . 
                   substr($cnpj, 8, 4) . '-' . 
                   substr($cnpj, 12);
        }
        
        return $cnpj;
    }
}