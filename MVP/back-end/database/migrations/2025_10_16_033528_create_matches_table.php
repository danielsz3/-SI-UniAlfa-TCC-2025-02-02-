<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchesTable extends Migration
{
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('animal_id')->constrained('animais')->onDelete('cascade');
            $table->enum('status', ['em_adocao', 'escolhido', 'rejeitado'])->default('em_adocao');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['usuario_id', 'animal_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('matches');
    }
};
