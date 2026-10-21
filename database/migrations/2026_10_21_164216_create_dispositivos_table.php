<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dispositivos', function (Blueprint $table) {
            // Identificador único que Laravel genera para cada dispositivo.
            $table->id();

            // Nombre que elegimos para reconocer el equipo.
            $table->string('nombre', 100);

            // Dirección IP registrada, sin permitir duplicados.
            $table->string('direccion_ip', 45)->unique();

            // Fechas de creación y última modificación del registro.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispositivos');
    }
};
