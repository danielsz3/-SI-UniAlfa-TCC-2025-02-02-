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
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('adocao_id')->nullable();
            $table->string('titulo');
            $table->text('mensagem');
            $table->enum('tipo', ['info', 'sucesso', 'aviso', 'erro'])->default('info');
            $table->boolean('lida')->default(false);
            $table->timestamp('data_leitura')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign keys
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('adocao_id')->references('id')->on('adocoes')->onDelete('set null');

            // Indexes
            $table->index(['usuario_id', 'lida']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};