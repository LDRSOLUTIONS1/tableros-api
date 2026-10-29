<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparativos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('excel_import_version_id')
                ->constrained('excel_import_versions')
                ->cascadeOnDelete();

            $table->char('registro_hash', 64)
                ->unique('comparativos_registro_hash_unique');

            $table->string('codigo', 50);
            $table->string('company', 150);
            $table->string('model', 150);
            $table->string('version', 150)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedBigInteger('bonus')->nullable();
            $table->unsignedBigInteger('price_with_bonus')->nullable();
            $table->unsignedBigInteger('sales_model_ytd_2025')->nullable();
            $table->unsignedBigInteger('sales_version_ytd_2025')->nullable();

            $table->string('market_share_by_segment', 100)->nullable();
            $table->string('foton', 150)->nullable();
            $table->string('origin', 100)->nullable();
            $table->string('avaliable', 100)->nullable();
            $table->string('segment', 100)->nullable();
            $table->string('sub_segment', 100)->nullable();
            $table->string('bodywork', 100)->nullable();
            $table->string('drive', 100)->nullable();
            $table->string('energy_system', 100)->nullable();
            $table->text('engine_baterry')->nullable();
            $table->string('displacement', 50)->nullable();
            $table->string('battery_capacity_kw_h', 50)->nullable();
            $table->string('transmission', 100)->nullable();
            $table->string('turbo', 50)->nullable();
            $table->string('autonomy', 50)->nullable();
            $table->string('aceleration_time', 50)->nullable();
            $table->string('maximum_speed', 50)->nullable();
            $table->string('hp', 50)->nullable();
            $table->string('lb_ft', 50)->nullable();
            $table->string('long', 50)->nullable();
            $table->string('tank', 50)->nullable();
            $table->string('payload_capacity', 50)->nullable();
            $table->string('towing_capacity', 50)->nullable();
            $table->string('seats', 50)->nullable();
            $table->string('vol_m3', 50)->nullable();
            $table->string('class', 100)->nullable();
            $table->string('gwv', 50)->nullable();
            $table->string('emission_standard', 100)->nullable();
            $table->string('range', 100)->nullable();
            $table->string('range_precio', 100)->nullable();
            $table->string('charging_time_hrs', 50)->nullable();
            $table->string('towing_capacity_precio', 100)->nullable();
            $table->string('precio_hp', 100)->nullable();
            $table->string('precio_lb_pie', 100)->nullable();
            $table->string('payload_precio', 100)->nullable();
            $table->string('n_airbags', 50)->nullable();
            $table->string('n_adas', 50)->nullable();
            $table->string('engine_brand', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparativos');
    }
};
