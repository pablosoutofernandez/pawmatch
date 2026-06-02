<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajuste de visibilidad: permite al usuario ocultarse del mapa que ven los
     * demás (sus perros no aparecerán en el mapa ni en el mini-mapa de otros).
     * Por defecto, visible.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mapa_visible')) {
                $table->boolean('mapa_visible')->default(true)->after('ubicacion_tiempo_real');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mapa_visible')) {
                $table->dropColumn('mapa_visible');
            }
        });
    }
};
