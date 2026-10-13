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
        Schema::create('catalogos.documentos', function (Blueprint $table) {
            $table->bigIncrements('id_documento');
            $table->string('nombre_documento', 150)->unique();
            $table->boolean('requiere_ambos_lados')->default(false);
            $table->boolean('permite_captura_texto')->default(false);
            $table->smallInteger('maximo_archivos')->default(1);
            $table->text('descripcion')->nullable();
            $table->timestampTz('fecha_registro')->useCurrent();
            $table->timestampTz('fecha_actualizacion')->nullable();
            $table->boolean('activo')->default(true);
        });

        DB::statement(
            'ALTER TABLE catalogos.documentos '
            . 'ADD CONSTRAINT chk_documentos_maximo_archivos CHECK (maximo_archivos BETWEEN 1 AND 10)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos.documentos');
    }
};
