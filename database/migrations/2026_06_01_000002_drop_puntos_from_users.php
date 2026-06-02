<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina la funcionalidad de "PawPoints" (columna users.puntos).
     *
     * Es condicional para que funcione tanto en bases de datos que ya tenían
     * la columna como en instalaciones nuevas (migrate:fresh), donde la
     * migración de creación de usuarios ya no la incluye.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'puntos')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('puntos');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'puntos')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('puntos')->default(0);
            });
        }
    }
};
