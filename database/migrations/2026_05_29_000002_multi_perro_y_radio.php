<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Radio de búsqueda persistente y compartido por Descubrir y Mapa.
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'radio_busqueda_km')) {
                $table->unsignedSmallInteger('radio_busqueda_km')->default(10)->after('ubicacion_tiempo_real');
            }
        });

        // 2) Los likes pasan a ser POR PERRO: un usuario puede dar like a varios
        //    perros del mismo dueño. Cambiamos la unicidad de (de_user, a_user)
        //    a (de_user, a_perro) para permitirlo.
        //
        //    En MySQL el unique compuesto (de_user_id, a_user_id) sirve también
        //    de índice para las FKs sobre esas columnas. Para poder dropearlo
        //    sin tocar las FKs, primero garantizamos un índice simple sobre
        //    cada columna implicada. Después podemos eliminar el compuesto.
        Schema::table('likes', function (Blueprint $table) {
            try {
                $table->index('de_user_id', 'likes_de_user_id_index');
            } catch (\Throwable $e) {
                // Ya existe.
            }
            try {
                $table->index('a_user_id', 'likes_a_user_id_index');
            } catch (\Throwable $e) {
                // Ya existe.
            }
        });

        Schema::table('likes', function (Blueprint $table) {
            try {
                $table->dropUnique('likes_de_user_id_a_user_id_unique');
            } catch (\Throwable $e) {
                // El índice puede no existir (BD recién creada con otra versión).
            }
        });

        // Crear la nueva unicidad por perro objetivo.
        Schema::table('likes', function (Blueprint $table) {
            try {
                $table->unique(['de_user_id', 'a_perro_id'], 'likes_de_user_a_perro_unique');
            } catch (\Throwable $e) {
                // Ya existe.
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'radio_busqueda_km')) {
                $table->dropColumn('radio_busqueda_km');
            }
        });

        Schema::table('likes', function (Blueprint $table) {
            try {
                $table->dropUnique('likes_de_user_a_perro_unique');
            } catch (\Throwable $e) {
            }
        });

        Schema::table('likes', function (Blueprint $table) {
            try {
                $table->unique(['de_user_id', 'a_user_id']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('likes', function (Blueprint $table) {
            try {
                $table->dropIndex('likes_de_user_id_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('likes_a_user_id_index');
            } catch (\Throwable $e) {
            }
        });
    }
};
