<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas_publico', function (Blueprint $table) {
            $table->id();

            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->string('tema', 255)->nullable();

            $table->unsignedSmallInteger('anio');

            $table->string('mes', 30);

            $table->unsignedTinyInteger('mes_numero')->nullable();

            $table->string('marca', 100)->nullable();

            $table->string('modelo', 150)->nullable();

            $table->string('tipo', 100)->nullable();

            $table->string('segmento_inegi', 100)->nullable();

            $table->string('origen', 50)->nullable();

            $table->string('pais_origen', 100)->nullable();

            $table->unsignedBigInteger('cantidad')->default(0);

            $table->string('mercado_inegi', 100)->nullable();

            $table->string('marca_mercado_foton', 20)->nullable();

            $table->string('unidad_foton_inegi', 100)->nullable();

            // Seg_P&V
            $table->string('seg_pv', 100)->nullable();

            // Origen/2
            $table->string('origen_2', 100)->nullable();

            $table->timestamps();

            $table->index('excel_import_version_id');
            $table->index(['anio', 'mes_numero']);
            $table->index(['marca', 'modelo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_publico');
    }
};
