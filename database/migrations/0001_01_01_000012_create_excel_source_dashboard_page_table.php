<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_source_dashboard_page', function (Blueprint $table) {
            $table->id();

            $table->foreignId('power_bi_dashboard_page_id')
                ->constrained('power_bi_dashboard_pages')
                ->cascadeOnDelete();

            $table->foreignId('excel_source_id')
                ->constrained('excel_sources')
                ->restrictOnDelete();

            $table->boolean('es_requerido')->default(true);

            $table->text('instrucciones')->nullable();

            $table->unsignedInteger('orden')->default(0);

            $table->timestamps();

            $table->unique(
                ['power_bi_dashboard_page_id', 'excel_source_id'],
                'page_excel_source_unique'
            );

            $table->index('excel_source_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_source_dashboard_page');
    }
};
