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
        Schema::create('contatos_ong', function (Blueprint $table) {
            $table->id('id_contato_ong');
            $table->unsignedBigInteger('id_ong');
            $table->foreign('id_ong')
                ->references('id_ong')
                ->on('ongs')
                ->onDelete('cascade');
            $table->enum('tipo_contato', ['telefone', 'email', 'redesocial']); 
            $table->string('valor_contato'); 

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contatos_ong');
    }
};
