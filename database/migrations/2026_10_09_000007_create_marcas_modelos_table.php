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
            $table->unsignedSmallInteger('anio')->nullable();
            $table->string('mes', 30)->nullable();
            $table->string('marca_im', 100)->nullable();
            $table->string('marca_inegi', 100)->nullable();
            $table->string('mod_im', 150)->nullable();
            $table->string('mod_inegi', 150)->nullable();
            $table->string('version_im', 150)->nullable();
            $table->string('segmento_e', 100)->nullable();
            $table->string('segmento_b', 100)->nullable();
            $table->string('segmento_c', 100)->nullable();
            $table->string('segmento_inegi', 100)->nullable();
            $table->string('tipo', 100)->nullable();
            $table->string('pais_marca', 100)->nullable();
            $table->string('origen', 100)->nullable();
            $table->string('pais_origen', 100)->nullable();
            $table->string('energia', 50)->nullable();
            $table->unsignedBigInteger('ventas')->nullable();
            $table->string('columna1', 100)->nullable();
            $table->unsignedTinyInteger('numero_mes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcas_modelos');
    }
};
