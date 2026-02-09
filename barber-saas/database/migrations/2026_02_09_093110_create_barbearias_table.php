<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barbearias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('telefone');
            $table->string('email')->unique();
            $table->string('cnpj')->unique();
            $table->string('cep');
            $table->string('endereco');
            $table->string('numero');
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado');
            $table->string('logo')->nullable();
            $table->time('horario_abertura_segunda_sex')->default('08:00:00');
            $table->time('horario_fechamento_segunda_sex')->default('18:00:00');
            $table->time('horario_abertura_sabado')->default('08:00:00');
            $table->time('horario_fechamento_sabado')->default('13:00:00');
            $table->boolean('abre_domingo')->default(false);
            $table->time('horario_abertura_domingo')->nullable();
            $table->time('horario_fechamento_domingo')->nullable();
            $table->integer('tempo_intervalo_agendamento')->default(30); // em minutos
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barbearias');
    }
};