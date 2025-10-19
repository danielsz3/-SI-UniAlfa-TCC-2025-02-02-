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
        Schema::create('contatos_ongs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ong');
            $table->enum('tipo', ['telefone', 'email', 'whatsapp', 'instagram', 'facebook', 'site', 'outro'])->nullable();
            $table->string('contato')->nullable();
            $table->string('link')->nullable();
            $table->text('descricao')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign key
            $table->foreign('id_ong')
                  ->references('id_ong')
                  ->on('ongs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contatos_ongs');
    }
};