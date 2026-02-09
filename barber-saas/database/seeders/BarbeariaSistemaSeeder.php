<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barbearia;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Servico;
use App\Models\Agendamento;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BarbeariaSistemaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seed do sistema SAAS de Barbearia...');
        
        // 1. CRIAR BARBEARIAS
        $this->command->info('🏢 Criando barbearias...');
        $barbearias = $this->criarBarbearias();
        
        // 2. CRIAR USUÁRIOS/ADMIN
        $this->command->info('👨‍💼 Criando usuários administradores...');
        $admin = $this->criarUsuarioAdmin();
        
        // 3. CRIAR BARBEIROS
        $this->command->info('💇 Criando barbeiros...');
        $barbeiros = $this->criarBarbeiros($barbearias);
        
        // 4. CRIAR SERVIÇOS
        $this->command->info('✂️ Criando serviços...');
        $servicos = $this->criarServicos($barbearias);
        
        // 5. CRIAR CLIENTES
        $this->command->info('👥 Criando clientes...');
        $clientes = $this->criarClientes($barbearias);
        
        // 6. CRIAR AGENDAMENTOS
        $this->command->info('📅 Criando agendamentos...');
        $this->criarAgendamentos($barbearias, $clientes, $servicos, $barbeiros);
        
        // 7. VINCULAR BARBEIROS ÀS BARBEARIAS
        $this->command->info('🔗 Vinculando barbeiros às barbearias...');
        $this->vincularBarbeiros($barbearias, $admin, $barbeiros);
        
        $this->command->info('✅ Seed concluído com sucesso!');
        $this->command->info('');
        $this->command->info('🔑 CREDENCIAIS DE ACESSO:');
        $this->command->info('   Admin: admin@barbeariasaas.com / password');
        $this->command->info('   Barbeiro 1: carlos.silva@email.com / password');
        $this->command->info('   Barbeiro 2: roberto.santos@email.com / password');
        $this->command->info('   Recepcionista: ana.souza@email.com / password');
        $this->command->info('');
        $this->command->info('🌐 Acesse: http://localhost:8000/admin');
    }
    
    private function criarBarbearias(): array
    {
        $barbearias = [];
        
        // Barbearia 1 - Estilo & Classe
        $barbearias[] = Barbearia::create([
            'nome' => 'Estilo & Classe Barbearia',
            'slug' => Str::slug('Estilo & Classe Barbearia') . '-' . Str::random(6),
            'telefone' => '(11) 98765-4321',
            'email' => 'contato@estiloeclasse.com.br',
            'cnpj' => '12.345.678/0001-95',
            'cep' => '01234-567',
            'endereco' => 'Avenida Paulista',
            'numero' => '1234',
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'horario_abertura_segunda_sex' => '08:00:00',
            'horario_fechamento_segunda_sex' => '20:00:00',
            'horario_abertura_sabado' => '09:00:00',
            'horario_fechamento_sabado' => '18:00:00',
            'abre_domingo' => false,
            'horario_abertura_domingo' => null,
            'horario_fechamento_domingo' => null,
            'tempo_intervalo_agendamento' => 30,
            'ativo' => true,
        ]);
        
        // Barbearia 2 - Barbearia do Zé
        $barbearias[] = Barbearia::create([
            'nome' => 'Barbearia do Zé',
            'slug' => Str::slug('Barbearia do Zé') . '-' . Str::random(6),
            'telefone' => '(21) 99876-5432',
            'email' => 'contato@barbeariadoze.com.br',
            'cnpj' => '23.456.789/0001-01',
            'cep' => '20000-000',
            'endereco' => 'Rua do Ouvidor',
            'numero' => '56',
            'bairro' => 'Centro',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'horario_abertura_segunda_sex' => '09:00:00',
            'horario_fechamento_segunda_sex' => '19:00:00',
            'horario_abertura_sabado' => '09:00:00',
            'horario_fechamento_sabado' => '17:00:00',
            'abre_domingo' => true,
            'horario_abertura_domingo' => '10:00:00',
            'horario_fechamento_domingo' => '16:00:00',
            'tempo_intervalo_agendamento' => 45,
            'ativo' => true,
        ]);
        
        // Barbearia 3 - Modern Cuts
        $barbearias[] = Barbearia::create([
            'nome' => 'Modern Cuts',
            'slug' => Str::slug('Modern Cuts') . '-' . Str::random(6),
            'telefone' => '(31) 98765-1234',
            'email' => 'contato@moderncuts.com.br',
            'cnpj' => '34.567.890/0001-23',
            'cep' => '30123-456',
            'endereco' => 'Rua da Bahia',
            'numero' => '789',
            'bairro' => 'Funcionários',
            'cidade' => 'Belo Horizonte',
            'estado' => 'MG',
            'horario_abertura_segunda_sex' => '08:30:00',
            'horario_fechamento_segunda_sex' => '19:30:00',
            'horario_abertura_sabado' => '08:00:00',
            'horario_fechamento_sabado' => '17:00:00',
            'abre_domingo' => false,
            'horario_abertura_domingo' => null,
            'horario_fechamento_domingo' => null,
            'tempo_intervalo_agendamento' => 60,
            'ativo' => true,
        ]);
        
        return $barbearias;
    }
    
    private function criarUsuarioAdmin(): User
    {
        return User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@barbeariasaas.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
    
    private function criarBarbeiros($barbearias): array
    {
        $barbeiros = [];
        
        // Barbeiro 1
        $barbeiros[] = User::create([
            'name' => 'Carlos Silva',
            'email' => 'carlos.silva@email.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Barbeiro 2
        $barbeiros[] = User::create([
            'name' => 'Roberto Santos',
            'email' => 'roberto.santos@email.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Barbeiro 3
        $barbeiros[] = User::create([
            'name' => 'Marcos Oliveira',
            'email' => 'marcos.oliveira@email.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Recepcionista
        $barbeiros[] = User::create([
            'name' => 'Ana Souza',
            'email' => 'ana.souza@email.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        return $barbeiros;
    }
    
    private function vincularBarbeiros($barbearias, $admin, $barbeiros): void
    {
        // Vincular admin a todas as barbearias
        foreach ($barbearias as $barbearia) {
            $barbearia->barbeiros()->attach($admin->id, [
                'papel' => 'admin',
                'ativo' => true,
            ]);
        }
        
        // Vincular barbeiros à primeira barbearia
        $barbearias[0]->barbeiros()->attach($barbeiros[0]->id, [
            'papel' => 'barbeiro',
            'ativo' => true,
        ]);
        
        $barbearias[0]->barbeiros()->attach($barbeiros[1]->id, [
            'papel' => 'barbeiro',
            'ativo' => true,
        ]);
        
        $barbearias[0]->barbeiros()->attach($barbeiros[3]->id, [
            'papel' => 'recepcionista',
            'ativo' => true,
        ]);
        
        // Vincular barbeiros à segunda barbearia
        $barbearias[1]->barbeiros()->attach($barbeiros[2]->id, [
            'papel' => 'barbeiro',
            'ativo' => true,
        ]);
        
        // Vincular barbeiros à terceira barbearia
        $barbearias[2]->barbeiros()->attach($barbeiros[0]->id, [
            'papel' => 'barbeiro',
            'ativo' => true,
        ]);
    }
    
    private function criarServicos($barbearias): array
    {
        $servicos = [];
        $servicosComuns = [
            [
                'nome' => 'Corte de Cabelo',
                'descricao' => 'Corte moderno com técnicas atualizadas',
                'preco' => 45.00,
                'duracao' => 40,
                'ordem' => 1,
            ],
            [
                'nome' => 'Barba',
                'descricao' => 'Barba feita com navalha e produtos especiais',
                'preco' => 35.00,
                'duracao' => 30,
                'ordem' => 2,
            ],
            [
                'nome' => 'Corte + Barba',
                'descricao' => 'Combo completo corte e barba',
                'preco' => 70.00,
                'duracao' => 60,
                'ordem' => 3,
            ],
            [
                'nome' => 'Pigmentação',
                'descricao' => 'Disfarce de fios brancos e rejuvenescimento',
                'preco' => 85.00,
                'duracao' => 50,
                'ordem' => 4,
            ],
            [
                'nome' => 'Sobrancelha',
                'descricao' => 'Design e manutenção de sobrancelhas',
                'preco' => 25.00,
                'duracao' => 20,
                'ordem' => 5,
            ],
            [
                'nome' => 'Hidratação Capilar',
                'descricao' => 'Tratamento para cabelos ressecados',
                'preco' => 55.00,
                'duracao' => 45,
                'ordem' => 6,
            ],
            [
                'nome' => 'Luzes',
                'descricao' => 'Aplicação de luzes masculinas',
                'preco' => 120.00,
                'duracao' => 90,
                'ordem' => 7,
            ],
            [
                'nome' => 'Massagem Capilar',
                'descricao' => 'Massagem relaxante no couro cabeludo',
                'preco' => 40.00,
                'duracao' => 30,
                'ordem' => 8,
            ],
        ];
        
        foreach ($barbearias as $barbearia) {
            foreach ($servicosComuns as $servico) {
                $servicos[] = Servico::create(array_merge($servico, [
                    'barbearia_id' => $barbearia->id,
                    'ativo' => true,
                ]));
            }
        }
        
        return $servicos;
    }
    
    private function criarClientes($barbearias): array
    {
        $clientes = [];
        
        foreach ($barbearias as $barbearia) {
            // Clientes para cada barbearia
            $clientesBarbearia = [
                [
                    'nome' => 'João Pedro Silva',
                    'telefone' => '(11) 98765-1234',
                    'email' => 'joao.pedro@email.com',
                    'data_nascimento' => '1985-05-15',
                    'observacoes' => 'Cliente preferencial, gosta de corte militar',
                ],
                [
                    'nome' => 'Ricardo Mendes',
                    'telefone' => '(11) 97654-2345',
                    'email' => 'ricardo.mendes@email.com',
                    'data_nascimento' => '1990-08-22',
                    'observacoes' => 'Sempre atrasa 5 minutos',
                ],
                [
                    'nome' => 'Fernando Costa',
                    'telefone' => '(11) 96543-3456',
                    'email' => 'fernando.costa@email.com',
                    'data_nascimento' => '1988-11-30',
                    'observacoes' => 'Prefere o barbeiro Carlos',
                ],
                [
                    'nome' => 'Lucas Pereira',
                    'telefone' => '(11) 95432-4567',
                    'email' => 'lucas.pereira@email.com',
                    'data_nascimento' => '1995-03-10',
                    'observacoes' => 'Cliente novo, corta a cada 15 dias',
                ],
                [
                    'nome' => 'Antônio Santos',
                    'telefone' => '(11) 94321-5678',
                    'email' => 'antonio.santos@email.com',
                    'data_nascimento' => '1979-12-05',
                    'observacoes' => 'Faz apenas barba',
                ],
                [
                    'nome' => 'Pedro Henrique',
                    'telefone' => '(11) 93210-6789',
                    'email' => 'pedro.henrique@email.com',
                    'data_nascimento' => '1992-07-18',
                    'observacoes' => 'Sempre faz hidratação',
                ],
                [
                    'nome' => 'Rafael Almeida',
                    'telefone' => '(11) 92109-7890',
                    'email' => 'rafael.almeida@email.com',
                    'data_nascimento' => '1983-04-25',
                    'observacoes' => 'Cliente desde 2020',
                ],
                [
                    'nome' => 'Gustavo Lima',
                    'telefone' => '(11) 91098-8901',
                    'email' => 'gustavo.lima@email.com',
                    'data_nascimento' => '1998-09-12',
                    'observacoes' => 'Estudante, horário após as 18h',
                ],
            ];
            
            foreach ($clientesBarbearia as $clienteData) {
                $clientes[] = Cliente::create(array_merge($clienteData, [
                    'barbearia_id' => $barbearia->id,
                    'ativo' => true,
                ]));
            }
        }
        
        return $clientes;
    }
    
    private function criarAgendamentos($barbearias, $clientes, $servicos, $barbeiros): void
    {
        $hoje = Carbon::today();
        $ontem = Carbon::yesterday();
        $amanha = Carbon::tomorrow();
        $semanaPassada = Carbon::today()->subDays(7);
        $semanaQueVem = Carbon::today()->addDays(7);
        
        $status = ['pendente', 'confirmado', 'em_atendimento', 'concluido', 'cancelado'];
        
        // Agendamentos para hoje
        for ($i = 0; $i < 8; $i++) {
            Agendamento::create([
                'barbearia_id' => $barbearias[0]->id,
                'cliente_id' => $clientes[$i]->id,
                'servico_id' => $servicos[$i % 6]->id,
                'barbeiro_id' => $barbeiros[$i % 2]->id,
                'data' => $hoje,
                'hora_inicio' => Carbon::parse('09:00')->addMinutes($i * 60)->format('H:i:s'),
                'hora_fim' => Carbon::parse('09:00')->addMinutes($i * 60 + 40)->format('H:i:s'),
                'status' => $i < 4 ? 'confirmado' : 'pendente',
                'valor' => $servicos[$i % 6]->preco,
                'observacoes' => $i === 0 ? 'Primeira visita' : null,
            ]);
        }
        
        // Agendamentos concluídos (ontem)
        for ($i = 0; $i < 6; $i++) {
            Agendamento::create([
                'barbearia_id' => $barbearias[0]->id,
                'cliente_id' => $clientes[$i]->id,
                'servico_id' => $servicos[$i % 6]->id,
                'barbeiro_id' => $barbeiros[$i % 2]->id,
                'data' => $ontem,
                'hora_inicio' => Carbon::parse('10:00')->addMinutes($i * 60)->format('H:i:s'),
                'hora_fim' => Carbon::parse('10:00')->addMinutes($i * 60 + 40)->format('H:i:s'),
                'status' => 'concluido',
                'valor' => $servicos[$i % 6]->preco,
                'observacoes' => null,
            ]);
        }
        
        // Agendamentos futuros (amanhã)
        for ($i = 0; $i < 5; $i++) {
            Agendamento::create([
                'barbearia_id' => $barbearias[0]->id,
                'cliente_id' => $clientes[$i + 2]->id,
                'servico_id' => $servicos[($i + 1) % 6]->id,
                'barbeiro_id' => $barbeiros[$i % 2]->id,
                'data' => $amanha,
                'hora_inicio' => Carbon::parse('14:00')->addMinutes($i * 60)->format('H:i:s'),
                'hora_fim' => Carbon::parse('14:00')->addMinutes($i * 60 + 40)->format('H:i:s'),
                'status' => 'pendente',
                'valor' => $servicos[($i + 1) % 6]->preco,
                'observacoes' => null,
            ]);
        }
        
        // Agendamentos cancelados (semana passada)
        for ($i = 0; $i < 2; $i++) {
            Agendamento::create([
                'barbearia_id' => $barbearias[0]->id,
                'cliente_id' => $clientes[$i]->id,
                'servico_id' => $servicos[$i]->id,
                'barbeiro_id' => $barbeiros[$i]->id,
                'data' => $semanaPassada,
                'hora_inicio' => Carbon::parse('11:00')->addMinutes($i * 60)->format('H:i:s'),
                'hora_fim' => Carbon::parse('11:00')->addMinutes($i * 60 + 40)->format('H:i:s'),
                'status' => 'cancelado',
                'valor' => $servicos[$i]->preco,
                'motivo_cancelamento' => 'Cliente desistiu',
                'cancelado_em' => $semanaPassada->subHours(2),
            ]);
        }
        
        // Agendamentos para outras barbearias
        for ($i = 1; $i < count($barbearias); $i++) {
            for ($j = 0; $j < 3; $j++) {
                Agendamento::create([
                    'barbearia_id' => $barbearias[$i]->id,
                    'cliente_id' => $clientes[$i * 8 + $j]->id,
                    'servico_id' => $servicos[$j]->id,
                    'barbeiro_id' => $barbeiros[2]->id,
                    'data' => $hoje->addDays($j),
                    'hora_inicio' => Carbon::parse('15:00')->addMinutes($j * 60)->format('H:i:s'),
                    'hora_fim' => Carbon::parse('15:00')->addMinutes($j * 60 + 40)->format('H:i:s'),
                    'status' => $status[$j % 5],
                    'valor' => $servicos[$j]->preco,
                    'observacoes' => "Barbearia {$barbearias[$i]->nome}",
                ]);
            }
        }
        
        // Agendamentos em atendimento (agora)
        Agendamento::create([
            'barbearia_id' => $barbearias[0]->id,
            'cliente_id' => $clientes[3]->id,
            'servico_id' => $servicos[2]->id,
            'barbeiro_id' => $barbeiros[0]->id,
            'data' => $hoje,
            'hora_inicio' => Carbon::now()->subMinutes(20)->format('H:i:s'),
            'hora_fim' => Carbon::now()->addMinutes(20)->format('H:i:s'),
            'status' => 'em_atendimento',
            'valor' => $servicos[2]->preco,
            'observacoes' => 'Em atendimento no momento',
        ]);
    }
}