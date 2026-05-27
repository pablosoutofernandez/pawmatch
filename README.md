# 🐾 PawMatch

Red social para dueños de perros — Proyecto Fin de Ciclo DAW

**Repositorio:** https://github.com/pablosoutofernandez/pawmatch

---

## Stack

- **Laravel 12** + **Livewire 3.5**
- **Breeze** (auth con Blade)
- **Spatie Permission** (roles y permisos)
- **Tailwind CSS 3**
- **MySQL** (via Laravel Sail / XAMPP)
- **Docker / Sail** (entorno de desarrollo en Linux)

---

## 🚀 Setup en Linux (con Sail)

### 1. Clonar el repositorio

```bash
git clone https://github.com/pablosoutofernandez/pawmatch.git
cd pawmatch
```

### 2. Instalar dependencias de Composer dentro de Docker

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 3. Configurar entorno

```bash
cp .env.example .env
```

Edita `.env` y asegúrate de tener:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=pawmatch
DB_USERNAME=sail
DB_PASSWORD=password
```

### 4. Levantar Sail

```bash
./vendor/bin/sail up -d
```

> Si el puerto 3306 está ocupado (por XAMPP u otro MySQL):
> ```bash
> sudo service mysql stop
> ./vendor/bin/sail up -d
> ```

### 5. Generar clave y migrar

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
```

### 6. Frontend

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Abre **http://localhost**

---

## 🚀 Setup en Windows (con XAMPP)

### 1. Clonar el repositorio

```bash
git clone https://github.com/pablosoutofernandez/pawmatch.git
cd pawmatch
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar entorno

Copia `.env.example` a `.env` y edítalo:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pawmatch
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Crear la base de datos

Abre XAMPP, arranca MySQL y desde phpMyAdmin crea la base de datos `pawmatch`. O por terminal:

```bash
mysql -u root -e "CREATE DATABASE pawmatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Generar clave y migrar

```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

### 6. Arrancar servidores

En dos terminales separadas:

```bash
php artisan serve
npm run dev
```

Abre **http://localhost:8000**

---

## 🔑 Usuarios demo

| Rol   | Email               | Contraseña |
|-------|---------------------|------------|
| Admin | admin@pawmatch.test | password   |
| User  | user@pawmatch.test  | password   |

---

## 📁 Estructura del proyecto

```
pawmatch/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                  ← Breeze auth controllers
│   │   │   ├── DashboardController.php
│   │   │   └── ProfileController.php
│   │   └── Requests/
│   │       ├── EditarPerfilRequest.php
│   │       └── EditarPerroRequest.php
│   ├── Livewire/                      ← Componentes Livewire como clases PHP
│   │   ├── DiscoverPerros.php
│   │   ├── ChatPerros.php
│   │   ├── MapaPerros.php
│   │   └── EditarPerfil.php
│   ├── Models/
│   │   ├── User.php                   ← con HasRoles (Spatie)
│   │   ├── Perro.php                  ← con scopePor* (porNombre, porRaza, porTamano)
│   │   ├── Like.php                   ← lógica de match bidireccional
│   │   ├── Conversacion.php
│   │   └── Mensaje.php
│   ├── Policies/
│   │   ├── DogPolicy.php              ← permisos ver/crear/editar/borrar (Spatie)
│   │   └── LikePolicy.php
│   └── Services/
│       └── AppServiceProvider.php     ← registra policies con Gate::policy()
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   └── PerroFactory.php
│   ├── migrations/
│   │   ├── 0001_..._users_table.php         ← con campos PawMatch
│   │   ├── 2026_..._perros_table.php
│   │   ├── 2026_..._social_tables.php       ← likes, conversaciones, mensajes
│   │   └── 2026_..._permission_tables.php   ← Spatie
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── PermissionSeeder.php             ← admin / premium / user / guest
│       ├── UserSeeder.php
│       └── PerroSeeder.php
│
├── resources/views/
│   ├── auth/                          ← Breeze (login, register, etc.)
│   ├── components/layouts/
│   │   └── app.blade.php              ← layout para componentes Livewire
│   ├── layouts/
│   │   ├── app.blade.php              ← layout principal con header
│   │   └── guest.blade.php            ← layout para auth
│   ├── livewire/                      ← vistas de los componentes Livewire
│   │   ├── discover-perros.blade.php
│   │   ├── chat-perros.blade.php
│   │   ├── mapa-perros.blade.php
│   │   └── editar-perfil.blade.php
│   ├── partials/
│   │   └── sidebar.blade.php          ← @include en cada vista Livewire
│   ├── profile/                       ← Breeze profile editor
│   └── dashboard.blade.php
│
└── routes/
    ├── web.php                        ← rutas → componentes Livewire directamente
    └── auth.php                       ← rutas de Breeze
```

---

## 🛣️ Rutas

| URL          | Cómo se sirve                                    |
|--------------|--------------------------------------------------|
| `/`          | Landing page (welcome.blade.php)                 |
| `/dashboard` | `DashboardController@index`                      |
| `/discover`  | `\App\Livewire\DiscoverPerros::class` (Livewire) |
| `/chat`      | `\App\Livewire\ChatPerros::class` (Livewire)     |
| `/mapa`      | `\App\Livewire\MapaPerros::class` (Livewire)     |
| `/perfil`    | `\App\Livewire\EditarPerfil::class` (Livewire)   |

---

## 🔒 Permisos (Spatie)

| Permiso  | admin | premium | user | guest |
|----------|:-----:|:-------:|:----:|:-----:|
| `ver`    | ✓     | ✓       | ✓    | ✓     |
| `crear`  | ✓     | ✓       | ✓    |       |
| `editar` | ✓     | ✓       | ✓    |       |
| `borrar` | ✓     | ✓       |      |       |

---

## 🧮 Algoritmo de compatibilidad

`Perro::compatibilidadCon(Perro $otro): int` — score 0-100:

- **30 pts** — energía similar
- **25 pts** — tamaño relativo (peso)
- **20 pts** — compatibilidad por tamaño declarada
- **15 pts** — ambos esterilizados
- **10 pts** — ambos vacunados

---

## 🛠️ Comandos útiles

```bash
# Resetear BD con datos demo
./vendor/bin/sail artisan migrate:fresh --seed   # Linux (Sail)
php artisan migrate:fresh --seed                  # Windows (XAMPP)

# Limpiar caché
./vendor/bin/sail artisan optimize:clear

# Ver rutas
./vendor/bin/sail artisan route:list

# Tinker
./vendor/bin/sail artisan tinker
```

---

## 🔄 Flujo de trabajo diario entre máquinas

**Antes de salir (commit y push):**
```bash
git add .
git commit -m "descripción de los cambios"
git push
```

**Al llegar a la otra máquina (pull):**
```bash
git pull
composer install      # solo si cambió composer.json
npm install           # solo si cambió package.json
php artisan migrate   # solo si hay migraciones nuevas
```

---

Proyecto académico — C.F.G.S. Desarrollo de Aplicaciones Web · IES Pazo da Mercé
