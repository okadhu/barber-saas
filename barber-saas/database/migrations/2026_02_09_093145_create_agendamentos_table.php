<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barbearia_id')->constrained()->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->foreignId('servico_id')->constrained()->onDelete('cascade');
            $table->foreignId('barbeiro_id')->constrained('users')->onDelete('cascade');
            $table->date('data');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->enum('status', [
                'pendente',
                'confirmado',
                'em_atendimento',
                'concluido',
                'cancelado',
                'nao_compareceu'
            ])->default('pendente');
            $table->decimal('valor', 8, 2);
            $table->text('observacoes')->nullable();
            $table->timestamp('confirmado_em')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['barbearia_id', 'data', 'status']);
            $table->index(['barbeiro_id', 'data', 'hora_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};