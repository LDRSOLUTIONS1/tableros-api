<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_import_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('excel_source_id')
                ->constrained('excel_sources')
                ->cascadeOnDelete();

            // 1, 2, 3, 4...
            $table->unsignedInteger('version_number');

            // Archivo original
            $table->string('original_filename', 255);

            // Archivo almacenado en storage
            $table->string('file_path', 500);

            // SHA256 del archivo
            $table->string('file_hash', 64)->nullable();

            $table->string('mime_type', 150)->nullable();

            $table->unsignedBigInteger('file_size')->nullable();

            /*
             * pending
             * processing
             * completed
             * failed
             */
            $table->string('status', 30)->default('pending');

            $table->unsignedBigInteger('total_rows')->default(0);

            // Usuario que hizo la importación
            $table->foreignId('imported_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('error_message')->nullable();

            $table->timestamp('imported_at')->nullable();

            $table->timestamps();

            // Evita tener dos v1 del mismo Excel
            $table->unique(
                ['excel_source_id', 'version_number'],
                'excel_source_version_unique'
            );

            // Evita importar exactamente el mismo archivo
            $table->unique(
                ['excel_source_id', 'file_hash'],
                'excel_source_hash_unique'
            );

            $table->index('status');
            $table->index('imported_at');
        });

        Schema::table('excel_sources', function (Blueprint $table) {
            $table->foreign('current_version_id')
                ->references('id')
                ->on('excel_import_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('excel_sources', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });

        Schema::dropIfExists('excel_import_versions');
    }
};
