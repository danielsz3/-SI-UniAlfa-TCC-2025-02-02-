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
        Schema::create('ongs', function (Blueprint $table) {
            $table->id('id_ong');
            $table->string('nome');
            $table->string('razao_social');
            $table->text('descricao')->nullable();
            $table->string('imagem')->nullable(); // Imagem de capa
            
            // Atributos de endereço
            $table->string('cep')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado')->nullable();
            $table->string('pais')->default('Brasil');
            
            // Dados bancários
            $table->string('banco')->nullable();
            $table->string('agencia')->nullable();
            $table->string('numero_conta')->nullable();
            $table->string('tipo_conta')->nullable();
            $table->string('chave_pix')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ongs');
    }
};