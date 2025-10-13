<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('imagens_lar_temporario', function (Blueprint $table) {
            // 1) soltar a PK atual
            $table->dropPrimary(); // solta PRIMARY KEY existente

            // 2) renomear a coluna antiga para id
            $table->renameColumn('imagens_lar_temporario', 'id');
        });

        // 3) tornar a coluna id AUTO_INCREMENT e PRIMARY KEY
        // Algumas versões do MySQL exigem ALTER via SQL bruto para AUTO_INCREMENT
        DB::statement('ALTER TABLE imagens_lar_temporario MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
    }

    public function down(): void
    {
        // rollback (opcional)
        Schema::table('imagens_lar_temporario', function (Blueprint $table) {
            $table->dropPrimary();
        });
        DB::statement('ALTER TABLE imagens_lar_temporario MODIFY COLUMN id BIGINT UNSIGNED NOT NULL');
        Schema::table('imagens_lar_temporario', function (Blueprint $table) {
            $table->renameColumn('id', 'imagens_lar_temporario');
            $table->primary('imagens_lar_temporario');
        });
    }
};