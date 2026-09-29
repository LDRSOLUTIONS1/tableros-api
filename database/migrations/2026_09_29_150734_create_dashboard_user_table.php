<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('dashboard_id')
                ->constrained('power_bi_dashboards')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['user_id', 'dashboard_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_user');
    }
};
