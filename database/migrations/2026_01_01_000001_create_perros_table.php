<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('nombre');
            $table->string('raza')->nullable();
            $table->unsignedTinyInteger('edad_anios')->default(0);
            $table->unsignedTinyInteger('edad_meses')->default(0);
            $table->decimal('peso_kg', 5, 2)->nullable();
            $table->enum('sexo', ['macho', 'hembra'])->nullable();
            $table->boolean('esterilizado')->default(false);

            $table->unsignedTinyInteger('energia')->default(3);   // 1-5
            $table->json('caracter')->nullable();

            $table->string('foto_principal')->nullable();
            $table->json('fotos')->nullable();

            $table->boolean('vacunado')->default(false);
            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perros');
    }
};
