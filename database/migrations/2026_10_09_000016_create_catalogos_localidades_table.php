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
        Schema::create('catalogos.localidades', function (Blueprint $table) {
            $table->bigIncrements('id_localidad');
            $table->bigInteger('id_estado');
            $table->string('nombre_localidad', 150);
            $table->string('codigo_postal', 10);
            $table->timestampTz('fecha_registro')->useCurrent();
            $table->timestampTz('fecha_actualizacion')->nullable();
            $table->boolean('activo')->default(true);

            $table->foreign('id_estado', 'fk_localidades_estado')
                ->references('id_estado')
                ->on('catalogos.estados')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos.localidades');
    }
};
