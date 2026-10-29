<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagenes_modelos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->char('registro_hash', 64)
                ->unique('imagenes_modelos_registro_hash_unique');

            $table->string('company', 150);
            $table->string('model', 150);
            $table->string('version', 150);
            $table->string('foton', 150);
            $table->text('image_url')->nullable();
            $table->text('image_logo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes_modelos');
    }
};
