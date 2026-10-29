<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcas_modelos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->char('registro_hash', 64)
                ->unique('marcas_modelos_registro_hash_unique');

            $table->string('tema', 255);
            $table->unsignedSmallInteger('anio');
            $table->string('mes', 30);
            $table->string('marca_im', 100);
            $table->string('marca_inegi', 100);
            $table->string('mod_im', 150);
            $table->string('mod_inegi', 150);
            $table->string('version_im', 150);
            $table->string('segmento_e', 100);
            $table->string('segmento_b', 100);
            $table->string('segmento_c', 100);
            $table->string('segmento_inegi', 100);
            $table->string('tipo', 100);
            $table->string('pais_marca', 100);
            $table->string('origen', 100);
            $table->string('pais_origen', 100);
            $table->string('energia', 50);
            $table->unsignedBigInteger('ventas')->default(0);
            $table->string('columna1', 100);
            $table->unsignedTinyInteger('numero_mes');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcas_modelos');
    }
};
