<?php

namespace App\Helpers;

class FormatHelper
{
    public static function formatarTelefone($telefone): string
    {
        if (empty($telefone)) {
            return '-';
        }
        
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        $length = strlen($telefone);
        
        if ($length === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 5) . '-' . 
                   substr($telefone, 7);
        } elseif ($length === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 4) . '-' . 
                   substr($telefone, 6);
        } elseif ($length === 9) {
            return substr($telefone, 0, 5) . '-' . 
                   substr($telefone, 5);
        } elseif ($length === 8) {
            return substr($telefone, 0, 4) . '-' . 
                   substr($telefone, 4);
        }
        
        return $telefone;
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
    
    // ADICIONE ESTE MÉTODO
    public static function formatarCEP($cep): string
    {
        if (empty($cep)) {
            return '-';
        }
        
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . 
                   substr($cep, 5);
        }
        
        return $cep;
    }
}