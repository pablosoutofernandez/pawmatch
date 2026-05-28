<?php

namespace App\Livewire;

use App\Models\Perro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class EditarPerfil extends Component
{
    use WithFileUploads;

    // Stepper: 1 = perfil usuario, 2 = perfil perro, 3 = listo
    public int $paso = 1;

    // ── Paso 1: Usuario ──────────────────────────────────────
    public string $name   = '';
    public string $email  = '';
    public string $bio    = '';
    public string $ciudad = '';
    public $fotoAvatar    = null;   // archivo temporal Livewire
    public ?string $avatarActual = null;

    // ── Paso 2: Perro ────────────────────────────────────────
    public ?int   $perroId           = null;
    public string $perroNombre       = '';
    public string $perroRaza         = '';
    public int    $perroEdadAnios    = 0;
    public float  $perroPesoKg       = 0;
    public string $perroSexo         = 'hembra';
    public bool   $perroEsterilizado = false;
    public bool   $perroVacunado     = false;
    public int    $perroEnergia      = 3;
    public array  $perroCaracter     = [];
    public string $perroDescripcion  = '';
    public $fotoPerro                = null;   // archivo temporal Livewire
    public ?string $fotoPerroActual  = null;

    public array $opcionesCaracter = [
        'tranquilo', 'juguetón', 'tímido', 'dominante',
        'amigable', 'protector', 'cariñoso', 'enérgico', 'curioso', 'sociable',
    ];

    public function mount(): void
    {
        $usuario = Auth::user();
        $this->name         = $usuario->name;
        $this->email        = $usuario->email;
        $this->bio          = $usuario->bio ?? '';
        $this->ciudad       = $usuario->ciudad ?? '';
        $this->avatarActual = $usuario->avatar_url ?? $usuario->avatar ?? null;

        $perro = $usuario->perros()->first();
        if ($perro) {
            $this->perroId           = $perro->id;
            $this->perroNombre       = $perro->nombre;
            $this->perroRaza         = $perro->raza ?? '';
            $this->perroEdadAnios    = $perro->edad_anios;
            $this->perroPesoKg       = (float) ($perro->peso_kg ?? 0);
            $this->perroSexo         = $perro->sexo ?? 'hembra';
            $this->perroEsterilizado = (bool) $perro->esterilizado;
            $this->perroVacunado     = (bool) $perro->vacunado;
            $this->perroEnergia      = $perro->energia;
            $this->perroCaracter     = $perro->caracter ?? [];
            $this->perroDescripcion  = $perro->descripcion ?? '';
            $this->fotoPerroActual   = $perro->foto_principal;
        }
    }

    // ─────────────────────────────────────────────────────────
    // Navegación del stepper
    // ─────────────────────────────────────────────────────────

    public function irPaso(int $paso): void
    {
        // Solo permite ir a pasos anteriores sin validar
        if ($paso < $this->paso) {
            $this->paso = $paso;
            return;
        }

        // Avanzar requiere validar el paso actual
        if ($this->paso === 1) {
            $this->guardarPerfil(false);
        } elseif ($this->paso === 2) {
            $this->guardarPerro(false);
        }
    }

    // ─────────────────────────────────────────────────────────
    // Validación por paso
    // ─────────────────────────────────────────────────────────

    public function reglasUsuario(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:100'],
            'email'       => ['required', 'email', Rule::unique('users', 'email')->ignore(Auth::id())],
            'bio'         => ['nullable', 'string', 'max:500'],
            'ciudad'      => ['nullable', 'string', 'max:100'],
            'fotoAvatar'  => ['nullable', 'image', 'max:3072'],   // 3 MB
        ];
    }

    public function reglasPerro(): array
    {
        return [
            'perroNombre'      => ['required', 'string', 'min:2', 'max:50'],
            'perroRaza'        => ['nullable', 'string', 'max:80'],
            'perroEdadAnios'   => ['nullable', 'integer', 'min:0', 'max:25'],
            'perroPesoKg'      => ['nullable', 'numeric', 'min:0', 'max:120'],
            'perroSexo'        => ['required', 'in:macho,hembra'],
            'perroEnergia'     => ['required', 'integer', 'between:1,5'],
            'perroCaracter'    => ['nullable', 'array'],
            'perroDescripcion' => ['nullable', 'string', 'max:600'],
            'fotoPerro'        => ['nullable', 'image', 'max:3072'],
        ];
    }

    // Validación en tiempo real (solo dispara la regla del campo concreto)
    public function updated(string $prop): void
    {
        $reglas = array_merge($this->reglasUsuario(), $this->reglasPerro());
        if (isset($reglas[$prop])) {
            $this->validateOnly($prop, $reglas);
        }
    }

    // ─────────────────────────────────────────────────────────
    // Guardar
    // ─────────────────────────────────────────────────────────

    public function guardarPerfil(bool $flash = true): void
    {
        $datos = $this->validate($this->reglasUsuario());

        $update = [
            'name'   => $datos['name'],
            'email'  => $datos['email'],
            'bio'    => $datos['bio'] ?? null,
            'ciudad' => $datos['ciudad'] ?? null,
        ];

        if ($this->fotoAvatar) {
            // Borrar avatar anterior si existe en storage
            if ($this->avatarActual && str_starts_with($this->avatarActual, '/storage/')) {
                $old = str_replace('/storage/', '', $this->avatarActual);
                Storage::disk('public')->delete($old);
            }
            $ruta = $this->fotoAvatar->store('avatars', 'public');
            $update['avatar_url']    = '/storage/'.$ruta;
            $this->avatarActual      = $update['avatar_url'];
            $this->fotoAvatar        = null;
        }

        Auth::user()->update($update);

        if ($flash) {
            session()->flash('success', '¡Perfil actualizado!');
        } else {
            $this->paso = 2;
        }
    }

    public function guardarPerro(bool $flash = true): void
    {
        $datos = $this->validate($this->reglasPerro());

        $attrs = [
            'user_id'             => Auth::id(),
            'nombre'              => $datos['perroNombre'],
            'raza'                => $datos['perroRaza'] ?? null,
            'edad_anios'          => $datos['perroEdadAnios'] ?? 0,
            'peso_kg'             => $datos['perroPesoKg'] ?? null,
            'sexo'                => $datos['perroSexo'],
            'esterilizado'        => $this->perroEsterilizado,
            'vacunado'            => $this->perroVacunado,
            'energia'             => $datos['perroEnergia'],
            'caracter'            => $datos['perroCaracter'] ?? [],
            'descripcion'         => $datos['perroDescripcion'] ?? null,
            'compatible_pequenos' => true,
            'compatible_grandes'  => true,
        ];

        if ($this->fotoPerro) {
            if ($this->fotoPerroActual && str_starts_with($this->fotoPerroActual, '/storage/')) {
                $old = str_replace('/storage/', '', $this->fotoPerroActual);
                Storage::disk('public')->delete($old);
            }
            $ruta = $this->fotoPerro->store('perros', 'public');
            $attrs['foto_principal'] = '/storage/'.$ruta;
            $this->fotoPerroActual   = $attrs['foto_principal'];
            $this->fotoPerro         = null;
        }

        if ($this->perroId) {
            Perro::find($this->perroId)?->update($attrs);
        } else {
            $perro = Perro::create($attrs);
            $this->perroId = $perro->id;
        }

        if ($flash) {
            session()->flash('success', '¡Perro guardado!');
        } else {
            $this->paso = 3;
        }
    }

    public function toggleCaracter(string $rasgo): void
    {
        if (in_array($rasgo, $this->perroCaracter, true)) {
            $this->perroCaracter = array_values(
                array_filter($this->perroCaracter, fn ($r) => $r !== $rasgo)
            );
        } else {
            $this->perroCaracter[] = $rasgo;
        }
    }

    public function render()
    {
        return view('livewire.editar-perfil');
    }
}
