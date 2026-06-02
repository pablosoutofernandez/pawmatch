<?php

namespace App\Livewire;

use App\Models\Conversacion;
use App\Models\Like;
use App\Models\Mensaje;
use App\Models\Perro;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ChatPerros extends Component
{
    public ?int   $conversacionActiva = null;
    public string $nuevoMensaje = '';

    public function mount(?int $conversacion = null): void
    {
        $primera = Auth::user()->conversaciones()->latest('updated_at')->first();
        $this->conversacionActiva = $conversacion ?? $primera?->id;

        if ($this->conversacionActiva) {
            $this->marcarLeido($this->conversacionActiva);
        }
    }

    public function abrirConversacion(int $id): void
    {
        $this->conversacionActiva = $id;
        $this->nuevoMensaje = '';
        $this->marcarLeido($id);
        $this->dispatch('scroll-bottom');
    }

    // Volver a la lista (útil en móvil).
    public function volverALista(): void
    {
        $this->conversacionActiva = null;
    }

    // Borrar un match: solo si lleva 48h sin actividad. Libera el slot.
    public function eliminarMatch(int $id): void
    {
        $conv = Auth::user()->conversaciones()
            ->with(['ultimoMensaje', 'participantes'])
            ->find($id);

        if (!$conv) {
            return;
        }

        if (!$conv->puedeEliminarse()) {
            $h = $conv->horasParaPoderEliminar();
            session()->flash('info', "Aún no puedes eliminar este match: podrás hacerlo tras 48 h sin actividad (faltan {$h} h).");
            return;
        }

        $otro = $conv->otroParticipante(Auth::id());
        $conv->eliminar();

        if ($this->conversacionActiva === $id) {
            $this->conversacionActiva = null;
        }

        session()->flash('info', 'Has eliminado tu match con '.($otro?->name ?? 'ese usuario').'.');
    }

    private function marcarLeido(int $convId): void
    {
        $conv = Auth::user()->conversaciones()->find($convId);
        $conv?->participantes()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);
    }

    public function rules(): array
    {
        return ['nuevoMensaje' => ['required', 'string', 'min:1', 'max:500']];
    }

    public function enviar(): void
    {
        $this->validateOnly('nuevoMensaje');

        if (!$this->conversacionActiva) {
            return;
        }

        $conv = Auth::user()->conversaciones()->find($this->conversacionActiva);
        if (!$conv) {
            abort(403, 'No participas en esta conversación');
        }

        Mensaje::create([
            'conversacion_id' => $conv->id,
            'remitente_id'    => Auth::id(),
            'cuerpo'          => trim($this->nuevoMensaje),
            'tipo'            => 'texto',
        ]);

        $conv->touch();
        $this->marcarLeido($conv->id);

        $this->nuevoMensaje = '';
        $this->dispatch('scroll-bottom');
    }

    // [userId => Collection<Perro>] con los perros del otro con los que ha
    // hecho match. Dos consultas en total para evitar N+1.
    private function perrosMatchMap(int $userId): array
    {
        $likes = Like::where('de_user_id', $userId)
            ->whereNotNull('match_at')
            ->whereNotNull('a_perro_id')
            ->get(['a_user_id', 'a_perro_id']);

        $perroIds = $likes->pluck('a_perro_id')->unique()->all();
        if (empty($perroIds)) {
            return [];
        }

        $perros = Perro::whereIn('id', $perroIds)->orderBy('id')->get()->keyBy('id');

        $map = [];
        foreach ($likes as $l) {
            $p = $perros->get($l->a_perro_id);
            if ($p) {
                $map[$l->a_user_id][$p->id] = $p;
            }
        }

        return array_map(fn ($arr) => collect(array_values($arr)), $map);
    }

    private function perroData(Collection $perros): array
    {
        return $perros->map(fn (Perro $p) => [
            'id'          => $p->id,
            'nombre'      => $p->nombre,
            'foto'        => $p->foto_url,
            'placeholder' => $p->placeholder_url,
        ])->all();
    }

    public function render()
    {
        $usuario = Auth::user();

        // Conversaciones con los datos mínimos (último mensaje + participantes).
        $convs = $usuario->conversaciones()
            ->with(['participantes', 'ultimoMensaje'])
            ->latest('updated_at')
            ->get();

        $convIds = $convs->pluck('id')->all();

        // Mensajes no leídos por conversación en UNA sola consulta.
        $noLeidosMap = collect();
        if (!empty($convIds)) {
            $noLeidosMap = DB::table('mensajes as m')
                ->join('conversacion_user as cu', function ($j) use ($usuario) {
                    $j->on('cu.conversacion_id', '=', 'm.conversacion_id')
                      ->where('cu.user_id', '=', $usuario->id);
                })
                ->whereIn('m.conversacion_id', $convIds)
                ->where('m.remitente_id', '!=', $usuario->id)
                ->where(function ($q) {
                    $q->whereNull('cu.last_read_at')
                      ->orWhereColumn('m.created_at', '>', 'cu.last_read_at');
                })
                ->groupBy('m.conversacion_id')
                ->selectRaw('m.conversacion_id as cid, count(*) as total')
                ->pluck('total', 'cid');
        }

        // Perros matcheados por usuario (2 consultas, sin N+1).
        $perrosPorUsuario = $this->perrosMatchMap($usuario->id);

        $conversaciones = $convs->map(function (Conversacion $conv) use ($usuario, $noLeidosMap, $perrosPorUsuario) {
            $otro   = $conv->otroParticipante($usuario->id);
            $perros = $this->perroData($perrosPorUsuario[$otro?->id] ?? collect());

            return (object) [
                'id'          => $conv->id,
                'user_id'     => $otro?->id,
                'nombre'      => $otro?->name ?? 'Desconocido',
                'iniciales'   => $otro?->getIniciales() ?? '?',
                'avatar'      => $otro?->avatar_photo,
                'premium'     => (bool) ($otro?->es_premium),
                'perros'      => $perros,
                'ultimo'      => $conv->ultimoMensaje?->cuerpo ?? 'Decid hola 👋',
                'hora'        => $conv->ultimoMensaje?->created_at?->format('H:i') ?? '',
                'no_leidos'   => (int) ($noLeidosMap[$conv->id] ?? 0),
                'activa'      => (bool) ($otro?->paseando_ahora),
                'puede_eliminar' => $conv->puedeEliminarse(),
            ];
        });

        $mensajes = [];
        $conversacionInfo = null;

        if ($this->conversacionActiva) {
            $conv = $usuario->conversaciones()
                ->with(['mensajes.remitente', 'participantes', 'ultimoMensaje'])
                ->find($this->conversacionActiva);

            if ($conv) {
                $otro = $conv->otroParticipante($usuario->id);
                $conversacionInfo = (object) [
                    'user_id'     => $otro?->id,
                    'nombre'      => $otro?->name ?? '?',
                    'iniciales'   => $otro?->getIniciales() ?? '?',
                    'avatar'      => $otro?->avatar_photo,
                    'premium'     => (bool) ($otro?->es_premium),
                    'activa'      => (bool) ($otro?->paseando_ahora),
                    'perros'      => $this->perroData($perrosPorUsuario[$otro?->id] ?? collect()),
                    'puede_eliminar'   => $conv->puedeEliminarse(),
                    'horas_eliminar'   => $conv->horasParaPoderEliminar(),
                ];
                $mensajes = $conv->mensajes->map(fn (Mensaje $m) => [
                    'out'    => $m->remitente_id === $usuario->id,
                    'cuerpo' => $m->cuerpo,
                    'hora'   => $m->created_at->format('H:i'),
                ])->toArray();
            }
        }

        return view('livewire.chat-perros', [
            'conversaciones'   => $conversaciones,
            'mensajes'         => $mensajes,
            'conversacionInfo' => $conversacionInfo,
            'horasInactividad' => Conversacion::HORAS_INACTIVIDAD,
        ]);
    }
}
