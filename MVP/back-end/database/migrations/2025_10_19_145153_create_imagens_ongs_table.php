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
        Schema::create('imagens_ongs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ong');
            $table->string('caminho');
            $table->string('nome_original')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
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
        Schema::dropIfExists('imagens_ongs');
    }
};