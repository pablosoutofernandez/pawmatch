<?php

namespace App\Livewire;

use App\Http\Requests\EditarPerfilRequest;
use App\Http\Requests\EditarPerroRequest;
use App\Models\Perro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class EditarPerfil extends Component
{
    public string $tabActiva = 'usuario';   // usuario | perro | preferencias | seguridad

    // Datos del usuario
    public string $name   = '';
    public string $email  = '';
    public string $bio    = '';
    public string $ciudad = '';

    // Datos del perro
    public ?int    $perroId        = null;
    public string  $perroNombre    = '';
    public string  $perroRaza      = '';
    public int     $perroEdadAnios = 0;
    public float   $perroPesoKg    = 0;
    public string  $perroSexo      = 'hembra';
    public bool    $perroEsterilizado = false;
    public bool    $perroVacunado     = false;
    public int     $perroEnergia      = 3;
    public array   $perroCaracter     = [];
    public string  $perroNotas        = '';

    // Carácter disponible
    public array $opcionesCaracter = [
        'tranquilo', 'jugueton', 'timido', 'dominante', 'amigable',
        'protector', 'cariñoso', 'energico',
    ];

    public function mount(): void
    {
        $usuario = Auth::user();

        $this->name   = $usuario->name;
        $this->email  = $usuario->email;
        $this->bio    = $usuario->bio ?? '';
        $this->ciudad = $usuario->ciudad ?? '';

        $perro = $usuario->perros()->first();

        if ($perro) {
            $this->perroId          = $perro->id;
            $this->perroNombre      = $perro->nombre;
            $this->perroRaza        = $perro->raza ?? '';
            $this->perroEdadAnios   = $perro->edad_anios;
            $this->perroPesoKg      = (float) ($perro->peso_kg ?? 0);
            $this->perroSexo        = $perro->sexo ?? 'hembra';
            $this->perroEsterilizado = (bool) $perro->esterilizado;
            $this->perroVacunado     = (bool) $perro->vacunado;
            $this->perroEnergia      = $perro->energia;
            $this->perroCaracter     = $perro->caracter ?? [];
            $this->perroNotas        = $perro->notas ?? '';
        }
    }

    public function cambiarTab(string $tab): void
    {
        $this->tabActiva = $tab;
        $this->resetValidation();
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

    /**
     * Reglas dinámicas según el tab activo.
     */
    public function rules(): array
    {
        if ($this->tabActiva === 'usuario') {
            return [
                'name'   => ['required', 'string', 'min:3', 'max:100'],
                'email'  => ['required', 'email', Rule::unique('users', 'email')->ignore(Auth::id())],
                'bio'    => ['nullable', 'string', 'max:500'],
                'ciudad' => ['nullable', 'string', 'max:100'],
            ];
        }

        if ($this->tabActiva === 'perro') {
            return [
                'perroNombre'       => ['required', 'string', 'min:2', 'max:50'],
                'perroRaza'         => ['nullable', 'string', 'max:80'],
                'perroEdadAnios'    => ['nullable', 'integer', 'min:0', 'max:25'],
                'perroPesoKg'       => ['nullable', 'numeric', 'min:0', 'max:120'],
                'perroSexo'         => ['required', 'in:macho,hembra'],
                'perroEnergia'      => ['required', 'integer', 'between:1,5'],
                'perroCaracter'     => ['nullable', 'array'],
                'perroNotas'        => ['nullable', 'string', 'max:500'],
            ];
        }

        return [];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function guardarPerfil(): void
    {
        $datos = $this->validate();

        Auth::user()->update([
            'name'   => $datos['name'],
            'email'  => $datos['email'],
            'bio'    => $datos['bio'] ?? null,
            'ciudad' => $datos['ciudad'] ?? null,
        ]);

        session()->flash('success', 'Perfil actualizado correctamente');
    }

    public function guardarPerro(): void
    {
        $datos = $this->validate();

        $atributos = [
            'user_id'       => Auth::id(),
            'nombre'        => $datos['perroNombre'],
            'raza'          => $datos['perroRaza'] ?? null,
            'edad_anios'    => $datos['perroEdadAnios'] ?? 0,
            'peso_kg'       => $datos['perroPesoKg'] ?? null,
            'sexo'          => $datos['perroSexo'],
            'esterilizado'  => $this->perroEsterilizado,
            'vacunado'      => $this->perroVacunado,
            'energia'       => $datos['perroEnergia'],
            'caracter'      => $datos['perroCaracter'] ?? [],
            'notas'         => $datos['perroNotas'] ?? null,
            'compatible_pequenos' => true,
            'compatible_grandes'  => true,
        ];

        if ($this->perroId) {
            Perro::find($this->perroId)?->update($atributos);
            session()->flash('success', 'Perfil del perro actualizado');
        } else {
            $perro = Perro::create($atributos);
            $this->perroId = $perro->id;
            session()->flash('success', '¡Perro registrado!');
        }
    }

    public function render()
    {
        return view('livewire.editar-perfil');
    }
}
