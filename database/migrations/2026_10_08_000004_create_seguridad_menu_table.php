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
        Schema::create('seguridad.menu', function (Blueprint $table) {
            $table->bigIncrements('id_menu');
            $table->bigInteger('id_menu_padre')->nullable();
            $table->boolean('es_padre')->default(false);
            $table->boolean('es_colapsable')->default(false);
            $table->string('nombre_menu', 150)->unique();
            $table->string('icono_clase', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->integer('orden')->nullable();
            $table->string('ruta', 150)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable();
            $table->boolean('activo')->default(true);

            $table->foreign('id_menu_padre')
                ->references('id_menu')->on('seguridad.menu')
                ->restrictOnDelete();

            $table->index('id_menu_padre', 'idx_menu_padre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguridad.menu');
    }
};
