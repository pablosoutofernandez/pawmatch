<?php

namespace Database\Seeders;

use App\Models\Conversacion;
use App\Models\Like;
use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Database\Seeder;

class SocialSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@pawmatch.test')->first();
        $user  = User::where('email', 'user@pawmatch.test')->first();
        $otros = User::whereNotIn('email', ['admin@pawmatch.test', 'user@pawmatch.test'])->get();

        if (!$admin || !$user || $otros->count() < 3) {
            $this->command->warn('SocialSeeder: faltan usuarios, se omite.');
            return;
        }

        // ── Likes PENDIENTES hacia admin y user (notificaciones) ──
        foreach ($otros->slice(0, 3) as $o) {
            $this->crearLikePendiente($o, $admin);
        }
        foreach ($otros->slice(3, 2) as $o) {
            $this->crearLikePendiente($o, $user);
        }

        // ── Un MATCH completo para admin (con mensajes) ──
        // Usamos índices que NO se solapan con los likes pendientes de arriba.
        $matchAdmin = $otros->get(5);
        if ($matchAdmin) {
            $this->crearMatchConMensajes($admin, $matchAdmin, [
                ['de' => $matchAdmin, 'txt' => '¡Hola! Vi que Lola es muy juguetona 🐶'],
                ['de' => $admin,      'txt' => '¡Hola! Sí, le encanta correr. ¿Quedamos?'],
                ['de' => $matchAdmin, 'txt' => '¿Mañana por la mañana en el parque?'],
            ]);
        }

        // ── Un MATCH completo para user (con mensajes) ──
        $matchUser = $otros->get(6) ?? $otros->last();
        if ($matchUser && $matchUser->id !== ($matchAdmin->id ?? null)) {
            $this->crearMatchConMensajes($user, $matchUser, [
                ['de' => $matchUser, 'txt' => 'Rocky tiene una pinta genial 🐕'],
                ['de' => $user,      'txt' => '¡Gracias! Tiene mucha energía jaja'],
            ]);
        }

        $this->command->info('✅ Datos sociales creados (likes pendientes + matches + mensajes).');
    }

    private function crearLikePendiente(User $de, User $a): void
    {
        Like::firstOrCreate(
            ['de_user_id' => $de->id, 'a_user_id' => $a->id],
            [
                'de_perro_id' => $de->perros()->first()?->id,
                'a_perro_id'  => $a->perros()->first()?->id,
            ]
        );
    }

    private function crearMatchConMensajes(User $a, User $b, array $mensajes): void
    {
        // Likes recíprocos con match
        $now = now();
        Like::firstOrCreate(
            ['de_user_id' => $a->id, 'a_user_id' => $b->id],
            ['de_perro_id' => $a->perros()->first()?->id, 'a_perro_id' => $b->perros()->first()?->id, 'match_at' => $now]
        );
        Like::firstOrCreate(
            ['de_user_id' => $b->id, 'a_user_id' => $a->id],
            ['de_perro_id' => $b->perros()->first()?->id, 'a_perro_id' => $a->perros()->first()?->id, 'match_at' => $now]
        );

        $conv = Conversacion::create(['match_at' => $now]);
        $conv->participantes()->attach([$a->id, $b->id]);

        $t = now()->subMinutes(count($mensajes) * 3);
        foreach ($mensajes as $m) {
            Mensaje::create([
                'conversacion_id' => $conv->id,
                'remitente_id'    => $m['de']->id,
                'cuerpo'          => $m['txt'],
                'tipo'            => 'texto',
                'created_at'      => $t,
                'updated_at'      => $t,
            ]);
            $t = $t->addMinutes(3);
        }
        $conv->update(['updated_at' => $t]);
    }
}
