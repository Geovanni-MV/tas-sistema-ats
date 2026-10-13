<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS catalogos');
        Schema::create('catalogos.escolaridades', function (Blueprint $table) {
            $table->bigIncrements('id_escolaridad');
            $table->string('nombre_escolaridad', 150)->unique();
            $table->text('descripcion')->nullable();
            $table->timestampTz('fecha_registro')->useCurrent();
            $table->timestampTz('fecha_actualizacion')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos.escolaridades');
    }
};
