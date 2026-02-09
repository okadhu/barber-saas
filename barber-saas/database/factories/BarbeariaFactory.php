<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BarbeariaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => $this->faker->company . ' Barbearia',
            'slug' => Str::slug($this->faker->company) . '-' . Str::random(6),
            'telefone' => $this->faker->numerify('(##) #####-####'),
            'email' => $this->faker->unique()->safeEmail(),
            'cnpj' => $this->gerarCnpjValido(),
            'cep' => $this->faker->numerify('#####-###'),
            'endereco' => $this->faker->streetName(),
            'numero' => $this->faker->buildingNumber(),
            'bairro' => $this->faker->citySuffix(),
            'cidade' => $this->faker->city(),
            'estado' => $this->faker->stateAbbr(),
            'logo' => null,
            'horario_abertura_segunda_sex' => '08:00:00',
            'horario_fechamento_segunda_sex' => '18:00:00',
            'horario_abertura_sabado' => '08:00:00',
            'horario_fechamento_sabado' => '13:00:00',
            'abre_domingo' => false,
            'tempo_intervalo_agendamento' => 30,
            'ativo' => true,
        ];
    }

    private function gerarCnpjValido(): string
    {
        // Gera um CNPJ válido para testes
        $n = [];
        
        // Gera 12 números aleatórios
        for ($i = 0; $i < 12; $i++) {
            $n[] = rand(0, 9);
        }
        
        // Calcula primeiro dígito verificador
        $soma = 0;
        $peso = 5;
        for ($i = 0; $i < 12; $i++) {
            $soma += $n[$i] * $peso;
            $peso = ($peso == 2) ? 9 : $peso - 1;
        }
        $resto = $soma % 11;
        $n[12] = ($resto < 2) ? 0 : 11 - $resto;
        
        // Calcula segundo dígito verificador
        $soma = 0;
        $peso = 6;
        for ($i = 0; $i < 13; $i++) {
            $soma += $n[$i] * $peso;
            $peso = ($peso == 2) ? 9 : $peso - 1;
        }
        $resto = $soma % 11;
        $n[13] = ($resto < 2) ? 0 : 11 - $resto;
        
        // Formata o CNPJ
        return sprintf(
            '%d%d.%d%d%d.%d%d%d/%d%d%d%d-%d%d',
            $n[0], $n[1], $n[2], $n[3], $n[4], $n[5], $n[6], $n[7], 
            $n[8], $n[9], $n[10], $n[11], $n[12], $n[13]
        );
    }
}