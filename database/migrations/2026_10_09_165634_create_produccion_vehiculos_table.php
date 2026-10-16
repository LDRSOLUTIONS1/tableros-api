<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produccion_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->string('prod_est', 255)->nullable();
            $table->unsignedSmallInteger('anio');
            $table->string('mes', 30);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 150)->nullable();
            $table->string('tipo', 100)->nullable();
            $table->string('segmento', 100)->nullable();
            $table->unsignedBigInteger('produccion')->default(0);
            $table->unsignedTinyInteger('numero_mes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produccion_vehiculos');
    }
};
