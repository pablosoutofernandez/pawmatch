<?php

namespace App\Livewire;

use App\Models\Conversacion;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ChatPerros extends Component
{
    public ?int   $conversacionActiva = null;
    public string $nuevoMensaje = '';

    public function mount(?int $conversacion = null): void
    {
        // Permite abrir directamente una conversación (?conversacion=ID)
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

        $conv->touch(); // sube la conversación al principio de la lista
        $this->marcarLeido($conv->id);

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
                $otro     = $conv->otroParticipante($usuario->id);
                $lastRead = $conv->participantes->firstWhere('id', $usuario->id)?->pivot->last_read_at;

                // No leídos = mensajes del OTRO posteriores a mi última lectura
                $noLeidos = $conv->mensajes()
                    ->where('remitente_id', '!=', $usuario->id)
                    ->when($lastRead, fn ($q) => $q->where('created_at', '>', $lastRead))
                    ->count();

                return (object) [
                    'id'        => $conv->id,
                    'nombre'    => $otro?->name ?? 'Desconocido',
                    'iniciales' => $otro?->getIniciales() ?? '?',
                    'avatar'    => $otro?->avatar_photo,
                    'ultimo'    => $conv->ultimoMensaje?->cuerpo ?? 'Decid hola 👋',
                    'hora'      => $conv->ultimoMensaje?->created_at?->format('H:i') ?? '',
                    'no_leidos' => $noLeidos,
                    'activa'    => (bool) ($otro?->paseando_ahora),
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
                    'avatar'    => $otro?->avatar_photo,
                    'activa'    => (bool) ($otro?->paseando_ahora),
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
        ]);
    }
}
