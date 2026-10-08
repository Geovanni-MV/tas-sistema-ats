<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS seguridad');
        Schema::create('seguridad.permisos_roles', function (Blueprint $table) {

            $table->bigIncrements('id_permiso_rol');
            $table->bigInteger('id_permiso')->nullable();
            $table->bigInteger('id_rol')->nullable();
            $table->bigInteger('id_menu')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable();
            $table->boolean('activo')->default(true);

            $table->foreign('id_permiso')
                ->references('id_permiso')
                ->on('seguridad.permisos')
                ->restrictOnDelete();

            $table->foreign('id_rol')
                ->references('id_rol')
                ->on('seguridad.roles')
                ->restrictOnDelete();

            $table->foreign('id_menu')
                ->references('id_menu')
                ->on('seguridad.menu')
                ->restrictOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguridad.permisos_roles');
    }
};
