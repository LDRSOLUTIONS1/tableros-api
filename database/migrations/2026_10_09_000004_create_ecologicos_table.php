<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecologicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->char('registro_hash', 64)
                ->unique('ecologicos_registro_hash_unique');

            $table->string('tema', 255);
            $table->unsignedSmallInteger('anio');
            $table->string('mes', 30);
            $table->string('entidad_federativa', 100);
            $table->unsignedBigInteger('unidades_vehiculares_electricas')
                ->default(0);
            $table->unsignedBigInteger('unidades_vehiculares_hibridas_plugin')
                ->default(0);
            $table->unsignedBigInteger('unidades_vehiculares_hibridas')
                ->default(0);
            $table->unsignedTinyInteger('numero_mes');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecologicos');
    }
};
