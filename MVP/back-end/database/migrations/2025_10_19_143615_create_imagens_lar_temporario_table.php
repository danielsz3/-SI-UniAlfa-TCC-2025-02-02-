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
        Schema::create('imagens_lar_temporario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lar_temporario');
            $table->string('caminho');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('nome_original')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign key
            $table->foreign('id_lar_temporario')
                  ->references('id')
                  ->on('lares_temporarios')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagens_lar_temporario');
    }
};