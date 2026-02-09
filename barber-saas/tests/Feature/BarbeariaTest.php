<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Barbearia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BarbeariaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar usuário admin para testes
        $this->user = User::factory()->create([
            'email' => 'admin@teste.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_criar_barbearia()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/admin/barbearias', [
            'nome' => 'Barbearia Teste',
            'telefone' => '11999999999',
            'email' => 'teste@barbearia.com',
            'cnpj' => '12345678000195',
            'cep' => '01234567',
            'endereco' => 'Rua Teste',
            'numero' => '123',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'horario_abertura_segunda_sex' => '08:00',
            'horario_fechamento_segunda_sex' => '18:00',
            'horario_abertura_sabado' => '08:00',
            'horario_fechamento_sabado' => '13:00',
            'abre_domingo' => false,
            'tempo_intervalo_agendamento' => 30,
            'ativo' => true,
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('barbearias', ['email' => 'teste@barbearia.com']);
    }

    public function test_listar_barbearias()
    {
        Barbearia::factory()->count(3)->create();
        
        $this->actingAs($this->user);
        $response = $this->get('/admin/barbearias');
        
        $response->assertStatus(200);
        $response->assertSee('Barbearias');
    }

    public function test_editar_barbearia()
    {
        $barbearia = Barbearia::factory()->create();
        
        $this->actingAs($this->user);
        $response = $this->put("/admin/barbearias/{$barbearia->id}", [
            'nome' => 'Barbearia Atualizada',
            'telefone' => $barbearia->telefone,
            'email' => $barbearia->email,
            'cnpj' => $barbearia->cnpj,
            'cep' => $barbearia->cep,
            'endereco' => $barbearia->endereco,
            'numero' => $barbearia->numero,
            'bairro' => $barbearia->bairro,
            'cidade' => $barbearia->cidade,
            'estado' => $barbearia->estado,
            'horario_abertura_segunda_sex' => '09:00',
            'horario_fechamento_segunda_sex' => '19:00',
            'horario_abertura_sabado' => '09:00',
            'horario_fechamento_sabado' => '14:00',
            'abre_domingo' => false,
            'tempo_intervalo_agendamento' => 45,
            'ativo' => true,
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('barbearias', [
            'id' => $barbearia->id,
            'nome' => 'Barbearia Atualizada',
            'tempo_intervalo_agendamento' => 45,
        ]);
    }

    public function test_excluir_barbearia()
    {
        $barbearia = Barbearia::factory()->create();
        
        $this->actingAs($this->user);
        $response = $this->delete("/admin/barbearias/{$barbearia->id}");
        
        $response->assertRedirect();
        $this->assertSoftDeleted('barbearias', ['id' => $barbearia->id]);
    }

    public function test_validacao_cnpj()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/admin/barbearias', [
            'nome' => 'Barbearia Teste',
            'telefone' => '11999999999',
            'email' => 'teste@barbearia.com',
            'cnpj' => '00000000000000', // CNPJ inválido
            'cep' => '01234567',
            'endereco' => 'Rua Teste',
            'numero' => '123',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'ativo' => true,
        ]);
        
        $response->assertSessionHasErrors('cnpj');
    }
}