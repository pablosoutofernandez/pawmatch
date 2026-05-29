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

    // Stepper: 1 = perfil usuario, 2 = perros, 3 = ubicación, 4 = listo
    public int $paso = 1;

    // ── Paso 1: Usuario ──────────────────────────────────────
    public string $name   = '';
    public string $email  = '';
    public string $bio    = '';
    public string $ciudad = '';
    public $fotoAvatar    = null;
    public ?string $avatarActual = null;

    // ── Paso 2: Perros (multi) ───────────────────────────────
    // Modo del paso 2: 'lista' (ver todos) o 'form' (crear/editar uno)
    public string $perroModo = 'lista';

    // Datos del perro en edición/creación
    public ?int   $perroId           = null;     // null = nuevo
    public string $perroNombre       = '';
    public string $perroRaza         = '';
    public int    $perroEdadAnios    = 0;
    public int    $perroEdadMeses    = 0;
    public float  $perroPesoKg       = 0;
    public string $perroSexo         = 'hembra';
    public bool   $perroEsterilizado = false;
    public bool   $perroVacunado     = false;
    public int    $perroEnergia      = 3;
    public array  $perroCaracter     = [];
    public string $perroDescripcion  = '';
    public $fotoPerro                = null;
    public ?string $fotoPerroActual  = null;

    // ── Paso 3: Ubicación ─────────────────────────────────────
    public ?float $latitud              = null;
    public ?float $longitud             = null;
    public bool   $ubicacionTiempoReal  = false;

    public array $opcionesCaracter = [
        'tranquilo', 'juguetón', 'tímido', 'dominante',
        'amigable', 'protector', 'cariñoso', 'enérgico', 'curioso', 'sociable',
    ];

    public int $maxCaracter = 5;

    protected function resolverUrl(?string $url): ?string
    {
        if (!$url) return null;
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        if (str_starts_with($url, '/storage/')) {
            return asset(ltrim($url, '/'));
        }
        return asset('storage/' . $url);
    }

    public function mount(int $paso = 1): void
    {
        $usuario = Auth::user();
        $this->name         = $usuario->name;
        $this->email        = $usuario->email;
        $this->bio          = $usuario->bio ?? '';
        $this->ciudad       = $usuario->ciudad ?? '';
        $this->avatarActual = $this->resolverUrl($usuario->avatar_url ?? $usuario->avatar ?? null);
        $this->latitud              = $usuario->latitud ? (float) $usuario->latitud : null;
        $this->longitud             = $usuario->longitud ? (float) $usuario->longitud : null;
        $this->ubicacionTiempoReal  = (bool) ($usuario->ubicacion_tiempo_real ?? false);

        // En paso 2: si el usuario no tiene perros, abrir directamente el form
        $this->perroModo = $usuario->perros()->exists() ? 'lista' : 'form';

        if (in_array($paso, [1, 2, 3], true)) {
            $this->paso = $paso;
        }
    }

    // ─────────────────────────────────────────────────────────
    // Navegación del stepper
    // ─────────────────────────────────────────────────────────

    public function irPaso(int $paso): void
    {
        if ($paso < $this->paso) {
            $this->paso = $paso;
            return;
        }

        if ($this->paso === 1) {
            $this->guardarPerfil(false);   // valida y avanza a 2
        } elseif ($this->paso === 2) {
            // Los perros se guardan individualmente; sólo avanzamos.
            $this->paso = 3;
        } elseif ($this->paso === 3) {
            $this->guardarUbicacion(false);
        }
    }

    // ─────────────────────────────────────────────────────────
    // Validación
    // ─────────────────────────────────────────────────────────

    public function reglasUsuario(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:100'],
            'email'       => ['required', 'email', Rule::unique('users', 'email')->ignore(Auth::id())],
            'bio'         => ['nullable', 'string', 'max:500'],
            'ciudad'      => ['nullable', 'string', 'max:100'],
            'fotoAvatar'  => ['nullable', 'image', 'max:3072'],
        ];
    }

    public function reglasPerro(): array
    {
        return [
            'perroNombre'      => ['required', 'string', 'min:2', 'max:50'],
            'perroRaza'        => ['nullable', 'string', 'max:80'],
            'perroEdadAnios'   => ['nullable', 'integer', 'min:0', 'max:25'],
            'perroEdadMeses'   => ['nullable', 'integer', 'min:0', 'max:11'],
            'perroPesoKg'      => ['nullable', 'numeric', 'min:0', 'max:120'],
            'perroSexo'        => ['required', 'in:macho,hembra'],
            'perroEnergia'     => ['required', 'integer', 'between:1,5'],
            'perroCaracter'    => ['nullable', 'array'],
            'perroDescripcion' => ['nullable', 'string', 'max:600'],
            'fotoPerro'        => ['nullable', 'image', 'max:3072'],
        ];
    }

    public function reglasUbicacion(): array
    {
        return [
            'latitud'             => ['nullable', 'numeric', 'between:-90,90'],
            'longitud'            => ['nullable', 'numeric', 'between:-180,180'],
            'ubicacionTiempoReal' => ['boolean'],
        ];
    }

    public function updated(string $prop): void
    {
        $reglas = array_merge($this->reglasUsuario(), $this->reglasPerro(), $this->reglasUbicacion());
        if (isset($reglas[$prop])) {
            $this->validateOnly($prop, $reglas);
        }
    }

    // ─────────────────────────────────────────────────────────
    // Guardar perfil de usuario
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
            if ($this->avatarActual && str_starts_with($this->avatarActual, '/storage/')) {
                $old = str_replace('/storage/', '', $this->avatarActual);
                Storage::disk('public')->delete($old);
            }
            $ruta = $this->fotoAvatar->store('avatars', 'public');
            $update['avatar_url']    = '/storage/'.$ruta;
            $this->avatarActual      = $this->resolverUrl('/storage/'.$ruta);
            $this->fotoAvatar        = null;
        }

        Auth::user()->update($update);

        if ($flash) {
            session()->flash('success', '¡Perfil actualizado!');
        } else {
            $this->paso = 2;
        }
    }

    public function guardarPerfilySalir(): void
    {
        $this->guardarPerfil(true);
        $this->redirect(route('mi-perfil'), navigate: true);
    }

    // ─────────────────────────────────────────────────────────
    // Gestión de PERROS (multi)
    // ─────────────────────────────────────────────────────────

    /** Limpia el formulario del perro y abre el modo "crear". */
    public function nuevoPerro(): void
    {
        $this->resetFormPerro();
        $this->perroModo = 'form';
    }

    /** Carga un perro existente en el formulario para editarlo. */
    public function editarPerro(int $id): void
    {
        $perro = Auth::user()->perros()->findOrFail($id);

        $this->perroId           = $perro->id;
        $this->perroNombre       = $perro->nombre;
        $this->perroRaza         = $perro->raza ?? '';
        $this->perroEdadAnios    = $perro->edad_anios ?? 0;
        $this->perroEdadMeses    = $perro->edad_meses ?? 0;
        $this->perroPesoKg       = (float) ($perro->peso_kg ?? 0);
        $this->perroSexo         = $perro->sexo ?? 'hembra';
        $this->perroEsterilizado = (bool) $perro->esterilizado;
        $this->perroVacunado     = (bool) $perro->vacunado;
        $this->perroEnergia      = $perro->energia;
        $this->perroCaracter     = $perro->caracter ?? [];
        $this->perroDescripcion  = $perro->descripcion ?? '';
        $this->fotoPerro         = null;
        $this->fotoPerroActual   = $this->resolverUrl($perro->foto_principal);

        $this->perroModo = 'form';
    }

    public function cancelarEdicionPerro(): void
    {
        $this->resetFormPerro();
        $this->perroModo = 'lista';
    }

    private function resetFormPerro(): void
    {
        $this->perroId           = null;
        $this->perroNombre       = '';
        $this->perroRaza         = '';
        $this->perroEdadAnios    = 0;
        $this->perroEdadMeses    = 0;
        $this->perroPesoKg       = 0;
        $this->perroSexo         = 'hembra';
        $this->perroEsterilizado = false;
        $this->perroVacunado     = false;
        $this->perroEnergia      = 3;
        $this->perroCaracter     = [];
        $this->perroDescripcion  = '';
        $this->fotoPerro         = null;
        $this->fotoPerroActual   = null;
        $this->resetValidation();
    }

    public function guardarPerro(): void
    {
        $datos = $this->validate($this->reglasPerro());

        $attrs = [
            'user_id'      => Auth::id(),
            'nombre'       => $datos['perroNombre'],
            'raza'         => $datos['perroRaza'] ?? null,
            'edad_anios'   => $datos['perroEdadAnios'] ?? 0,
            'edad_meses'   => $datos['perroEdadMeses'] ?? 0,
            'peso_kg'      => $datos['perroPesoKg'] ?? null,
            'sexo'         => $datos['perroSexo'],
            'esterilizado' => $this->perroEsterilizado,
            'vacunado'     => $this->perroVacunado,
            'energia'      => $datos['perroEnergia'],
            'caracter'     => $datos['perroCaracter'] ?? [],
            'descripcion'  => $datos['perroDescripcion'] ?? null,
        ];

        if ($this->fotoPerro) {
            if ($this->fotoPerroActual && str_starts_with($this->fotoPerroActual, '/storage/')) {
                $old = str_replace('/storage/', '', $this->fotoPerroActual);
                Storage::disk('public')->delete($old);
            }
            $ruta = $this->fotoPerro->store('perros', 'public');
            $attrs['foto_principal'] = '/storage/'.$ruta;
            $this->fotoPerro         = null;
        }

        if ($this->perroId) {
            Auth::user()->perros()->find($this->perroId)?->update($attrs);
            session()->flash('success', '¡'.$attrs['nombre'].' actualizado!');
        } else {
            Perro::create($attrs);
            session()->flash('success', '¡'.$attrs['nombre'].' añadido a tu manada!');
        }

        $this->resetFormPerro();
        $this->perroModo = 'lista';
    }

    public function eliminarPerro(int $id): void
    {
        $perro = Auth::user()->perros()->find($id);
        if ($perro) {
            $nombre = $perro->nombre;
            // Borrar foto subida si la había
            if ($perro->foto_principal && str_starts_with($perro->foto_principal, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $perro->foto_principal));
            }
            $perro->delete();
            session()->flash('success', 'Has eliminado a '.$nombre.'.');
        }
        if ($this->perroId === $id) {
            $this->resetFormPerro();
        }
        $this->perroModo = 'lista';
    }

    // ─────────────────────────────────────────────────────────
    // Ubicación
    // ─────────────────────────────────────────────────────────

    public function guardarUbicacion(bool $flash = true): void
    {
        $this->validate($this->reglasUbicacion());

        Auth::user()->update([
            'latitud'               => $this->latitud,
            'longitud'              => $this->longitud,
            'ubicacion_tiempo_real' => $this->ubicacionTiempoReal,
        ]);

        if ($flash) {
            session()->flash('success', '¡Ubicación guardada!');
        } else {
            $this->paso = 4;
        }
    }

    public function setUbicacionDesdeJS(float $lat, float $lng): void
    {
        $this->latitud  = $lat;
        $this->longitud = $lng;
    }

    public function borrarUbicacion(): void
    {
        $this->latitud             = null;
        $this->longitud            = null;
        $this->ubicacionTiempoReal = false;

        Auth::user()->update([
            'latitud'               => null,
            'longitud'              => null,
            'ubicacion_tiempo_real' => false,
        ]);
    }

    public function toggleCaracter(string $rasgo): void
    {
        if (in_array($rasgo, $this->perroCaracter, true)) {
            $this->perroCaracter = array_values(
                array_filter($this->perroCaracter, fn ($r) => $r !== $rasgo)
            );
        } elseif (count($this->perroCaracter) < $this->maxCaracter) {
            $this->perroCaracter[] = $rasgo;
        }
    }

    public function render()
    {
        return view('livewire.editar-perfil', [
            'misPerros' => Auth::user()->perros()->orderBy('id')->get(),
        ]);
    }
}
