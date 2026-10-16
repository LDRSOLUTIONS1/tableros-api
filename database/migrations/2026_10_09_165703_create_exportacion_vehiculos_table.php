<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exportacion_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->char('registro_hash', 64)
                ->unique('exportacion_vehiculos_registro_hash_unique');

            $table->string('prod_est', 255)->nullable();
            $table->string('cobertura', 100)->nullable();
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes_numero')->nullable();
            $table->string('mes', 30);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 150)->nullable();
            $table->string('tipo', 100)->nullable();
            $table->string('segmento', 100)->nullable();
            $table->unsignedBigInteger('exportacion')->default(0);
            $table->string('pais_destino', 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exportacion_vehiculos');
    }
};
