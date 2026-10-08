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
        Schema::create('seguridad.usuarios', function (Blueprint $table) {
            $table->bigIncrements('id_usuario');

            $table->string('nombres', 150);
            $table->string('apellidos', 150);
            $table->string('telefono', 15);
            $table->string('correo_electronico', 150)->unique();

            $table->bigInteger('id_empresa')->nullable();
            $table->bigInteger('id_rol')->nullable();

            $table->text('clave_hash');

            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable();

            $table->boolean('activo')->default(true);

            $table->foreign('id_rol')
                ->references('id_rol')
                ->on('seguridad.roles')
                ->restrictOnDelete();

            $table->foreign('id_empresa')
                ->references('id_empresa')
                ->on('reclutamiento.empresas')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguridad.usuarios');
    }
};
