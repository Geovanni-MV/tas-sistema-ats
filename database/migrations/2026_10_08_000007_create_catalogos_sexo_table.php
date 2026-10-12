<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS catalogos');
        Schema::create('catalogos.sexo', function (Blueprint $table) {
            $table->bigIncrements('id_sexo');
            $table->string('nombre_sexo', 150);
            $table->text('descripcion')->nullable();
            $table->timestampTz('fecha_registro')->useCurrent();
            $table->timestampTz('fecha_actualizacion')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos.sexo');
    }
};
