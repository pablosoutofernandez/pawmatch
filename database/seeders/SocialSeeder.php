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
        $otros = User::whereNotIn('email', ['admin@pawmatch.test', 'user@pawmatch.test'])
            ->whereHas('perros')->get()->values();

        if (!$admin || !$user || $otros->count() < 5) {
            $this->command->warn('SocialSeeder: faltan usuarios con perros, se omite.');
            return;
        }

        // ── Likes PENDIENTES hacia admin (notificaciones) ──
        // Cada "otro" da like al primer perro de admin (Lola)
        $lola = $admin->perros()->where('nombre', 'Lola')->first() ?? $admin->perros()->first();
        $coco = $admin->perros()->where('nombre', 'Coco')->first();

        foreach ($otros->slice(0, 2) as $o) {
            $this->crearLike($o, $admin, $o->perros->first(), $lola, false);
        }
        // Un tercero le da like a Coco (otro perro de admin) → demuestra likes a distintos perros del mismo dueño
        if ($coco) {
            $this->crearLike($otros->get(2), $admin, $otros->get(2)->perros->first(), $coco, false);
        }

        // ── Likes pendientes hacia user ──
        $rocky = $user->perros()->first();
        foreach ($otros->slice(3, 2) as $o) {
            $this->crearLike($o, $user, $o->perros->first(), $rocky, false);
        }

        // ── MATCH MULTI-PERRO: matchAdmin da like a Lola Y a Coco; admin le devuelve like ──
        $matchAdmin = $otros->get(5) ?? $otros->last();
        if ($matchAdmin && $lola) {
            $perroOtro = $matchAdmin->perros->first();
            // El otro le da like a Lola
            $this->crearLike($matchAdmin, $admin, $perroOtro, $lola, true);
            // El otro le da like también a Coco (mismo usuario, perro distinto)
            if ($coco) {
                $this->crearLike($matchAdmin, $admin, $perroOtro, $coco, true);
            }
            // Admin le devuelve like a su perro
            $this->crearLike($admin, $matchAdmin, $lola, $perroOtro, true);

            $this->crearConvConMensajes($admin, $matchAdmin, [
                ['de' => $matchAdmin, 'txt' => '¡Hola! Vi que Lola y Coco son geniales 🐶🐕'],
                ['de' => $admin,      'txt' => '¡Hola! Sí, son inseparables. ¿Quedamos?'],
                ['de' => $matchAdmin, 'txt' => '¿Mañana por la mañana en el parque?'],
            ]);
        }

        // ── MATCH para user (un solo perro) ──
        $matchUser = $otros->get(6) ?? $otros->last();
        if ($matchUser && $matchUser->id !== ($matchAdmin->id ?? null) && $rocky) {
            $perroOtro = $matchUser->perros->first();
            $this->crearLike($matchUser, $user, $perroOtro, $rocky, true);
            $this->crearLike($user, $matchUser, $rocky, $perroOtro, true);

            $this->crearConvConMensajes($user, $matchUser, [
                ['de' => $matchUser, 'txt' => 'Rocky tiene una pinta genial 🐕'],
                ['de' => $user,      'txt' => '¡Gracias! Tiene mucha energía jaja'],
            ]);
        }

        $this->command->info('✅ Datos sociales creados (likes pendientes + matches multi-perro + mensajes).');
    }

    private function crearLike(?User $de, ?User $a, $dePerro, $aPerro, bool $conMatch): void
    {
        if (!$de || !$a || !$dePerro || !$aPerro) return;

        Like::firstOrCreate(
            ['de_user_id' => $de->id, 'a_perro_id' => $aPerro->id],
            [
                'a_user_id'   => $a->id,
                'de_perro_id' => $dePerro->id,
                'match_at'    => $conMatch ? now() : null,
            ]
        );
    }

    private function crearConvConMensajes(User $a, User $b, array $mensajes): void
    {
        $conv = Conversacion::whereHas('participantes', fn($q) => $q->where('user_id', $a->id))
            ->whereHas('participantes', fn($q) => $q->where('user_id', $b->id))
            ->first();

        if (!$conv) {
            $conv = Conversacion::create(['match_at' => now()]);
            $conv->participantes()->attach([$a->id, $b->id]);
        }

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
