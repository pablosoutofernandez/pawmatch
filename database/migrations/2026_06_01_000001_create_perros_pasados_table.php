<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Perros que un usuario ha "pasado" en Descubrir (no quiere volver a ver).
        // No mezclamos con la tabla likes para mantener su semántica limpia.
        Schema::create('perros_pasados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('perro_id')->constrained('perros')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'perro_id'], 'perros_pasados_user_perro_unique');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perros_pasados');
    }
};
