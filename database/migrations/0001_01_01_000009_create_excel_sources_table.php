<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_sources', function (Blueprint $table) {
            $table->id();

            // Identificador interno
            $table->string('codigo', 100)->unique();

            // Nombre que verá el usuario
            $table->string('nombre', 255);

            $table->text('descripcion')->nullable();

            // Versión que actualmente utiliza el sistema
            $table->unsignedBigInteger('current_version_id')->nullable();

            $table->tinyInteger('estado')
                ->default(2)
                ->comment('0=Eliminado, 1=Inactivo, 2=Activo');
            $table->timestamps();

            $table->index('current_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_sources');
    }
};
