<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('power_bi_dashboard_pages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('power_bi_dashboard_id')
                ->constrained('power_bi_dashboards')
                ->cascadeOnDelete();

            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();

            $table->unsignedInteger('orden')->default(0);
            $table->unsignedTinyInteger('estado')->default(2);

            $table->timestamps();

            $table->unique(
                ['power_bi_dashboard_id', 'nombre'],
                'dashboard_page_unique'
            );

            $table->index(
                ['power_bi_dashboard_id', 'estado'],
                'dashboard_page_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('power_bi_dashboard_pages');
    }
};
