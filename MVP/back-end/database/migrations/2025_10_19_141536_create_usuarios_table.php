<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); // Já cria como 'id' (não 'id_usuario')
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('cpf', 11)->unique();
            $table->date('data_nascimento');
            $table->string('telefone', 11)->nullable();
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->softDeletes(); // Cria deleted_at
            $table->timestamps(); // Cria created_at e updated_at
            $table->rememberToken(); // Adiciona remember_token (vi no print)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};