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
        Schema::create('ongs_enderecos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ong');
            $table->unsignedBigInteger('endereco_id');
            $table->softDeletes();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_ong')
                  ->references('id_ong')
                  ->on('ongs')
                  ->onDelete('cascade');

            $table->foreign('endereco_id')
                  ->references('id')
                  ->on('enderecos')
                  ->onDelete('cascade');

            // Evita duplicação
            $table->unique(['id_ong', 'endereco_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ongs_enderecos');
    }
};