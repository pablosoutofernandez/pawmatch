<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AdminUsuarios extends Component
{
    use WithPagination;

    public string $buscar = '';
    public ?int   $confirmandoId = null;

    public function mount(): void
    {
        // La ruta ya filtra por rol, pero por si acaso lo repetimos aquí.
        if (!Auth::user()?->hasRole('admin')) {
            abort(403, 'Solo administradores.');
        }
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function pedirConfirmacion(int $id): void
    {
        $this->confirmandoId = $id;
    }

    public function cancelarConfirmacion(): void
    {
        $this->confirmandoId = null;
    }

    // Reglas: solo admins, no a ti mismo, no a otro admin.
    public function eliminar(int $id): void
    {
        $yo = Auth::user();

        if (!$yo->hasRole('admin')) {
            abort(403);
        }

        if ($id === $yo->id) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta de admin.');
            $this->confirmandoId = null;
            return;
        }

        $u = User::find($id);
        if (!$u) {
            $this->confirmandoId = null;
            return;
        }

        if ($u->hasRole('admin')) {
            session()->flash('error', 'No puedes eliminar a otro administrador.');
            $this->confirmandoId = null;
            return;
        }

        $nombre = $u->name;

        // Borrado manual: no asumimos cascada en todas las FKs.
        $u->likesEnviados()->delete();
        $u->likesRecibidos()->delete();
        $u->perros()->each(function ($p) { $p->forceDelete(); });
        foreach ($u->conversaciones as $conv) {
            $conv->mensajes()->delete();
            $conv->participantes()->detach();
            $conv->delete();
        }
        $u->delete();

        $this->confirmandoId = null;
        session()->flash('success', 'Usuario "'.$nombre.'" eliminado.');
        $this->resetPage();
    }

    public function render()
    {
        $q = User::query()
            ->with('roles')
            ->withCount(['perros', 'likesEnviados', 'likesRecibidos'])
            ->orderBy('id');

        if ($this->buscar !== '') {
            $q->where(function ($w) {
                $w->where('name', 'like', '%'.$this->buscar.'%')
                  ->orWhere('email', 'like', '%'.$this->buscar.'%');
            });
        }

        return view('livewire.admin-usuarios', [
            'usuarios' => $q->paginate(15),
        ]);
    }
}
