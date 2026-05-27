<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Likes ──────────────────────────────────────────────
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('de_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('a_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('de_perro_id')->nullable()->constrained('perros')->nullOnDelete();
            $table->foreignId('a_perro_id')->nullable()->constrained('perros')->nullOnDelete();
            $table->timestamp('match_at')->nullable();
            $table->timestamps();

            $table->unique(['de_user_id', 'a_user_id']);
        });

        // ── Conversaciones ─────────────────────────────────────
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id();
            $table->timestamp('match_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversacion_user', function (Blueprint $table) {
            $table->foreignId('conversacion_id')->constrained('conversaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            $table->primary(['conversacion_id', 'user_id']);
        });

        // ── Mensajes ───────────────────────────────────────────
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('conversaciones')->cascadeOnDelete();
            $table->foreignId('remitente_id')->constrained('users')->cascadeOnDelete();
            $table->text('cuerpo');
            $table->string('tipo')->default('texto');   // texto | foto | quedada
            $table->json('metadata')->nullable();
            $table->timestamp('leido_at')->nullable();
            $table->timestamps();

            $table->index(['conversacion_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
        Schema::dropIfExists('conversacion_user');
        Schema::dropIfExists('conversaciones');
        Schema::dropIfExists('likes');
    }
};
