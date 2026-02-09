<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarCNPJ implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $value);
        
        // Verifica se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            $fail('O CNPJ deve conter 14 dígitos.');
            return;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            $fail('CNPJ inválido.');
            return;
        }
        
        // Valida primeiro dígito verificador
        $soma = 0;
        $peso = 5;
        
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $peso;
            $peso = ($peso == 2) ? 9 : $peso - 1;
        }
        
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;
        
        if ($cnpj[12] != $digito1) {
            $fail('CNPJ inválido.');
            return;
        }
        
        // Valida segundo dígito verificador
        $soma = 0;
        $peso = 6;
        
        for ($i = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $peso;
            $peso = ($peso == 2) ? 9 : $peso - 1;
        }
        
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        
        if ($cnpj[13] != $digito2) {
            $fail('CNPJ inválido.');
            return;
        }
    }
}