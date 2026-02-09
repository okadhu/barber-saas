<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barbearia_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barbearia_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('papel', ['admin', 'barbeiro', 'recepcionista'])->default('barbeiro');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            $table->unique(['barbearia_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barbearia_user');
    }
};