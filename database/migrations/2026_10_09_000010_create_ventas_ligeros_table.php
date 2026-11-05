<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas_ligeros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->char('registro_hash', 64)
                ->unique('ventas_ligeros_registro_hash_unique');

            $table->string('tema', 255);
            $table->unsignedSmallInteger('anio');
            $table->string('mes', 30);
            $table->string('marca', 100);
            $table->string('modelo', 150);
            $table->string('tipo', 100);
            $table->string('segmento_inegi', 100);
            $table->string('origen', 100);
            $table->string('pais_origen', 100);
            $table->unsignedBigInteger('cantidad')->default(0);
            $table->unsignedTinyInteger('numero_mes');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_ligeros');
    }
};
