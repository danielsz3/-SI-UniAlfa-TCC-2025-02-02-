<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagens_ongs', function (Blueprint $table) {
            $table->id();
            // FK correta: ong_id referenciando ongs.id
            $table->foreignId('ong_id')->constrained('ongs')->cascadeOnDelete();

            $table->string('caminho');
            $table->string('nome_original')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagens_ongs');
    }
};