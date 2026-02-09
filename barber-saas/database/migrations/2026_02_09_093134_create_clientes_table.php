<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barbearia_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            $table->string('telefone');
            $table->string('email')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('foto')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['barbearia_id', 'telefone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};