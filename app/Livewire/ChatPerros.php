<?php

namespace App\Livewire;

use App\Models\Conversacion;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ChatPerros extends Component
{
    public ?int    $conversacionActiva = null;
    public string  $nuevoMensaje = '';

    // Demo: si no hay conversaciones reales, mostramos una simulada
    public array $mensajesDemo = [];

    public function mount(): void
    {
        // Primera conversación o demo
        $conversacion = Auth::user()->conversaciones()->first();
        $this->conversacionActiva = $conversacion?->id;

        if (!$conversacion) {
            $this->mensajesDemo = [
                ['out' => false, 'cuerpo' => 'Hola! Vi que tu perro es muy juguetón 🐶 ¿Os gustaría quedar?', 'hora' => '10:15'],
                ['out' => true,  'cuerpo' => '¡Hola! Sí, nos encantaría', 'hora' => '10:18'],
                ['out' => false, 'cuerpo' => '¿Mañana por la mañana en el parque?', 'hora' => '10:24'],
            ];
        }
    }

    public function abrirConversacion(int $id): void
    {
        $this->conversacionActiva = $id;
        $this->nuevoMensaje = '';
    }

    public function rules(): array
    {
        return [
            'nuevoMensaje' => ['required', 'string', 'min:1', 'max:500'],
        ];
    }

    public function enviar(): void
    {
        $this->validateOnly('nuevoMensaje');

        if (!$this->conversacionActiva) {
            // Modo demo: solo añade al array local
            $this->mensajesDemo[] = [
                'out'    => true,
                'cuerpo' => $this->nuevoMensaje,
                'hora'   => now()->format('H:i'),
            ];
            $this->nuevoMensaje = '';
            return;
        }

        $conv = Auth::user()->conversaciones()->find($this->conversacionActiva);
        if (!$conv) {
            abort(403, 'No participas en esta conversación');
        }

        Mensaje::create([
            'conversacion_id' => $conv->id,
            'remitente_id'    => Auth::id(),
            'cuerpo'          => $this->nuevoMensaje,
            'tipo'            => 'texto',
        ]);

        $conv->touch();
        $this->nuevoMensaje = '';

        $this->dispatch('scroll-bottom');
    }

    public function render()
    {
        $usuario = Auth::user();

        $conversaciones = $usuario->conversaciones()
            ->with(['participantes', 'ultimoMensaje'])
            ->latest('updated_at')
            ->get()
            ->map(function (Conversacion $conv) use ($usuario) {
                $otro = $conv->otroParticipante($usuario->id);
                return (object) [
                    'id'            => $conv->id,
                    'nombre'        => $otro?->name ?? 'Desconocido',
                    'iniciales'     => $otro?->getIniciales() ?? '?',
                    'ultimo'        => $conv->ultimoMensaje?->cuerpo ?? 'Iniciar conversación...',
                    'hora'          => $conv->ultimoMensaje?->created_at?->format('H:i') ?? '',
                    'no_leidos'     => 0,
                    'activa'        => true,
                ];
            });

        $mensajes = [];
        $conversacionInfo = null;

        if ($this->conversacionActiva) {
            $conv = $usuario->conversaciones()
                ->with(['mensajes.remitente', 'participantes'])
                ->find($this->conversacionActiva);
            if ($conv) {
                $otro = $conv->otroParticipante($usuario->id);
                $conversacionInfo = (object) [
                    'nombre'    => $otro?->name ?? '?',
                    'iniciales' => $otro?->getIniciales() ?? '?',
                ];
                $mensajes = $conv->mensajes->map(fn (Mensaje $m) => [
                    'out'    => $m->remitente_id === $usuario->id,
                    'cuerpo' => $m->cuerpo,
                    'hora'   => $m->created_at->format('H:i'),
                ])->toArray();
            }
        }

        // Si no hay conversaciones reales, fallback al demo
        if ($conversaciones->isEmpty()) {
            $conversaciones = collect([(object) [
                'id'        => 0,
                'nombre'    => 'Rocky & Carlos M.',
                'iniciales' => 'RC',
                'ultimo'    => '¡Perfecto! Nos vemos mañana 🐾',
                'hora'      => '10:32',
                'no_leidos' => 2,
                'activa'    => true,
            ]]);
            $mensajes = $this->mensajesDemo;
            $conversacionInfo = (object) ['nombre' => 'Rocky & Carlos M.', 'iniciales' => 'RC'];
        }

        return view('livewire.chat-perros', [
            'conversaciones'   => $conversaciones,
            'mensajes'         => $mensajes,
            'conversacionInfo' => $conversacionInfo,
        ]);
    }
}
