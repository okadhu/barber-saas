<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarTelefone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove tudo que não é número
        $telefone = preg_replace('/[^0-9]/', '', $value);
        
        // Verifica se tem 10 ou 11 dígitos (com DDD)
        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            $fail('O telefone deve conter 10 ou 11 dígitos.');
            return;
        }
        
        // Verifica DDD (primeiros 2 dígitos)
        $ddd = substr($telefone, 0, 2);
        $dddsValidos = [
            '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '21', '22', '24', '27', '28', '31', '32', '33', '34', '35', '37', '38',
            '41', '42', '43', '44', '45', '46', '47', '48', '49',
            '51', '53', '54', '55',
            '61', '62', '63', '64', '65', '66', '67', '68', '69',
            '71', '73', '74', '75', '77', '79',
            '81', '82', '83', '84', '85', '86', '87', '88', '89',
            '91', '92', '93', '94', '95', '96', '97', '98', '99'
        ];
        
        if (!in_array($ddd, $dddsValidos)) {
            $fail('DDD inválido.');
            return;
        }
        
        // Verifica se o número começa com 9 (celular) ou 2-5 (fixo)
        $primeiroDigitoNumero = substr($telefone, 2, 1);
        
        // Se for celular (começa com 9)
        if (strlen($telefone) === 11 && $primeiroDigitoNumero !== '9') {
            $fail('Número de celular deve começar com 9.');
            return;
        }
        
        // Se for fixo (começa com 2-5)
        if (strlen($telefone) === 10 && !in_array($primeiroDigitoNumero, ['2', '3', '4', '5'])) {
            $fail('Número de telefone fixo deve começar com 2, 3, 4 ou 5.');
            return;
        }
    }
}