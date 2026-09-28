<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();

            $table->string('name', 100);        // clave interna única, ej: "brands"
            $table->string('title', 255);        // texto visible en el menú, ej: "Brands"
            $table->string('segment', 255);      // ruta/segmento del front, ej: "Brands"
            $table->string('icon', 100)->nullable(); // clave que resuelve iconMap.js, ej: "sell"
            $table->unsignedInteger('order')->default(0);

            $table->tinyInteger('estado')
                ->default(2)
                ->comment('0=Eliminado, 1=Inactivo, 2=Activo');
            $table->timestamps();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
