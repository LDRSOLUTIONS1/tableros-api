<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios_ofertas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->char('registro_hash', 64)
                ->unique('precios_ofertas_registro_hash_unique');

            $table->string('company', 150);
            $table->string('model', 150);
            $table->string('version', 150);
            $table->string('foton', 150);
            $table->unsignedBigInteger('precio');
            $table->unsignedBigInteger('bono');
            $table->unsignedTinyInteger('meses_sin_intereses')->nullable();
            $table->decimal('cargo_por_apertura', 12, 2)->nullable();
            $table->decimal('tasas_de_interes', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_ofertas');
    }
};
