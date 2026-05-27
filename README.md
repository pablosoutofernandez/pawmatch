# 🐾 PawMatch

Red social para dueños de perros — Proyecto Fin de Ciclo DAW

## Stack

- **Laravel 12** + **Livewire 3.5**
- **Breeze** (auth con Blade)
- **Spatie Permission** (roles y permisos)
- **Tailwind CSS 3**
- **SQLite** (desarrollo) o MySQL (XAMPP)

---

## 🚀 Setup

### 1. Instalar dependencias

```bash
composer install
npm install
```

### 2. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Base de datos

**Opción A — SQLite (recomendado, sin instalar nada):**

```bash
# En Linux/Mac
touch database/database.sqlite
# En Windows (PowerShell)
# New-Item database/database.sqlite -ItemType File
```

El `.env.example` ya está configurado para SQLite por defecto.

**Opción B — MySQL con XAMPP:**

1. Crea la base de datos `pawmatch` en phpMyAdmin
2. Edita `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pawmatch
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### 4. Migrar y cargar datos demo

```bash
php artisan migrate:fresh --seed
```

### 5. Arrancar servidores

En dos terminales separadas:

```bash
php artisan serve
```
```bash
npm run dev
```

Abre **http://localhost:8000**

---

## 🔑 Usuarios demo

| Rol     | Email                  | Contraseña |
|---------|------------------------|------------|
| Admin   | admin@pawmatch.test    | password   |
| User    | user@pawmatch.test     | password   |

---

## 📁 Estructura del proyecto

```
pawmatch3/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/             ← Breeze auth controllers
│   │   │   ├── DashboardController.php
│   │   │   └── ProfileController.php
│   │   └── Requests/
│   │       ├── EditarPerfilRequest.php
│   │       └── EditarPerroRequest.php
│   ├── Livewire/                 ← Componentes Livewire como clases PHP
│   │   ├── DiscoverPerros.php
│   │   ├── ChatPerros.php
│   │   ├── MapaPerros.php
│   │   └── EditarPerfil.php
│   ├── Models/
│   │   ├── User.php              ← con HasRoles (Spatie)
│   │   ├── Perro.php             ← con scopePor* (porNombre, porRaza, porTamano)
│   │   ├── Like.php              ← lógica de match bidireccional
│   │   ├── Conversacion.php
│   │   └── Mensaje.php
│   ├── Policies/
│   │   ├── DogPolicy.php         ← permisos ver/crear/editar/borrar (Spatie)
│   │   └── LikePolicy.php
│   └── Services/
│       └── AppServiceProvider.php ← registra policies con Gate::policy()
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   └── PerroFactory.php
│   ├── migrations/
│   │   ├── 0001_..._users_table.php       ← con campos PawMatch
│   │   ├── 2026_..._perros_table.php
│   │   ├── 2026_..._social_tables.php     ← likes, conversaciones, mensajes
│   │   └── 2026_..._permission_tables.php  ← Spatie
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── PermissionSeeder.php   ← admin / premium / user / guest
│       ├── UserSeeder.php
│       └── PerroSeeder.php
│
├── resources/views/
│   ├── auth/                     ← Breeze (login, register, etc.)
│   ├── components/               ← Breeze UI components
│   ├── layouts/
│   │   ├── app.blade.php         ← layout principal con header
│   │   └── guest.blade.php       ← layout para auth
│   ├── livewire/                 ← vistas de los componentes Livewire
│   │   ├── discover-perros.blade.php
│   │   ├── chat-perros.blade.php
│   │   ├── mapa-perros.blade.php
│   │   └── editar-perfil.blade.php
│   ├── partials/
│   │   └── sidebar.blade.php     ← incluido con @include en cada Livewire view
│   ├── profile/                  ← Breeze profile editor
│   └── dashboard.blade.php
│
└── routes/
    ├── web.php                   ← rutas → componentes Livewire directamente
    └── auth.php                  ← rutas de Breeze
```

---

## 🛣️ Rutas

| URL          | Cómo se sirve                                       |
|--------------|-----------------------------------------------------|
| `/dashboard` | `DashboardController@index` (vista normal)          |
| `/discover`  | `\App\Livewire\DiscoverPerros::class` (Livewire)    |
| `/chat`      | `\App\Livewire\ChatPerros::class` (Livewire)        |
| `/mapa`      | `\App\Livewire\MapaPerros::class` (Livewire)        |
| `/perfil`    | `\App\Livewire\EditarPerfil::class` (Livewire)      |

---

## 🔒 Permisos (Spatie)

| Permiso  | admin | premium | user | guest |
|----------|:-----:|:-------:|:----:|:-----:|
| `ver`    | ✓     | ✓       | ✓    | ✓     |
| `crear`  | ✓     | ✓       | ✓    |       |
| `editar` | ✓     | ✓       | ✓    |       |
| `borrar` | ✓     | ✓       |      |       |

Comprobaciones en componentes Livewire:
```php
if (Auth::user()->cannot('crear')) {
    abort(403, 'No tienes permiso');
}
```

O con policies:
```php
$this->authorize('update', $perro);
```

---

## 🧮 Algoritmo de compatibilidad

`Perro::compatibilidadCon(Perro $otro): int` — devuelve un score 0-100 basado en:

- **30 pts** — energía similar
- **25 pts** — tamaño relativo (peso)
- **20 pts** — compatibilidad por tamaño declarada
- **15 pts** — ambos esterilizados
- **10 pts** — ambos vacunados

---

## 🛠️ Comandos útiles

```bash
# Resetear base de datos con datos demo
php artisan migrate:fresh --seed

# Limpiar caché
php artisan optimize:clear

# Ver rutas
php artisan route:list

# Tinker (consola interactiva)
php artisan tinker
```

---

Proyecto académico — C.F.G.S. Desarrollo de Aplicaciones Web · IES Pazo da Mercé
