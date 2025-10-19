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
        Schema::create('imagens_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evento_id');
            $table->string('caminho');
            $table->string('nome_original')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign key
            $table->foreign('evento_id')
                  ->references('id')
                  ->on('eventos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagens_eventos');
    }
};