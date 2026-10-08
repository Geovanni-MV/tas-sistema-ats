<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS seguridad');
        Schema::create('seguridad.roles', function (Blueprint $table) {
            $table->bigIncrements('id_rol');
            $table->string('nombre_rol', 150)->unique();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguridad.roles');
    }
};
