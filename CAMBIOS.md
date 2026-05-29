# PawMatch — Cambios aplicados (ubicación + matches/chat + notificaciones)

Este documento resume lo que se ha implementado y cómo arrancar el proyecto.

## 1. Ubicación

- **Tu ubicación se captura del navegador** (clave para la cercanía). Al cargar
  cualquier página autenticada se pide permiso de geolocalización y, si lo
  concedes, se guarda en tu usuario; además se actualiza **en tiempo real**
  mientras tienes la pestaña abierta (`watchPosition`).
  - `resources/views/partials/geolocate.blade.php` — script de captura.
  - `app/Http/Controllers/UbicacionController.php` + ruta `POST /ubicacion`.
- **Distancias reales** (fórmula de Haversine) en Descubrir, Mapa y Dashboard.
  Antes eran valores aleatorios. Ver `User::distanciaKm()`.
- **El mapa usa datos reales de la BD**: cada perro se sitúa con la ubicación de
  su dueño, con su distancia y compatibilidad reales; tu marcador se mueve en
  vivo y puedes filtrar por radio y por capas (perros / parques).
  - `app/Livewire/MapaPerros.php` (reescrito) y su vista.
  - `public/js/pawmap.js` — lógica de Leaflet (externalizada).

> Nota: la geolocalización del navegador solo funciona en **localhost** o bajo
> **HTTPS** (requisito de los navegadores). En `http://localhost` funciona.

## 2. Matches, notificaciones y chat (modelo NO tipo Tinder)

- Cuando alguien da like a tu perfil, te aparece una **notificación**
  *"X le ha dado like a tu perfil"* en `/notificaciones` (con badge en el menú).
- Desde ahí puedes **"Dar like de vuelta"**: eso crea el **match** y la
  **conversación**, y ya podéis hablar en el chat. También puedes ignorar.
  - `app/Livewire/Notificaciones.php` + vista + ruta `/notificaciones`.
- El **chat funciona de verdad** (ya no usa datos de demostración): lista de
  conversaciones reales, mensajes persistidos, contador de no leídos, marcar
  como leído y refresco automático.
  - `app/Livewire/ChatPerros.php` (reescrito) y su vista.
- La lógica de match vive en `App\Models\Like::darLike()` (endurecida para no
  duplicar conversaciones).

Datos de ejemplo (likes pendientes + matches con mensajes) se crean con
`database/seeders/SocialSeeder.php`.

## 3. Cómo arrancar

### Opción A — Laravel Sail / MySQL (como en el README original)
```bash
composer install            # (con PHP 8.4; si usas 8.3 añade --ignore-platform-reqs)
cp .env.example .env        # si no tienes .env
php artisan key:generate
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
npm install && npm run build
```

### Opción B — Rápida con SQLite (sin MySQL)
En `.env` pon:
```env
DB_CONNECTION=sqlite
# y comenta DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```
Luego:
```bash
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install && npm run build
php artisan serve
```

### Usuarios de prueba
- Admin:   `admin@pawmatch.test` / `password`
- Usuario: `user@pawmatch.test` / `password`

Ambos arrancan con notificaciones de likes pendientes y un match con mensajes,
para que puedas ver el flujo completo nada más entrar.

## Archivos nuevos / modificados (resumen)
- Nuevos: `UbicacionController.php`, `Livewire/Notificaciones.php`,
  `views/livewire/notificaciones.blade.php`, `views/partials/geolocate.blade.php`,
  `public/js/pawmap.js`, `database/seeders/SocialSeeder.php`.
- Modificados: `Models/User.php`, `Models/Like.php`, `Livewire/MapaPerros.php`,
  `Livewire/ChatPerros.php`, `Livewire/DiscoverPerros.php`,
  `Http/Controllers/DashboardController.php`, `routes/web.php`,
  `views/layouts/app.blade.php`, `views/partials/sidebar.blade.php`,
  `views/livewire/mapa-perros.blade.php`, `views/livewire/chat-perros.blade.php`,
  `database/seeders/DatabaseSeeder.php`.
