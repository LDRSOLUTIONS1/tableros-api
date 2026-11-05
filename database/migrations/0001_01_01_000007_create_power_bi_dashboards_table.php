<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('power_bi_dashboards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('power_bi_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('nombre');

            $table->text('descripcion')->nullable();

            $table->text('url');

            $table->string('fuente')->nullable();

            $table->integer('orden')->default(0);

            $table->tinyInteger('estado')
                ->default(2)
                ->comment('0=Eliminado, 1=Inactivo, 2=Activo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('power_bi_dashboards');
    }
};
