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

---

## 5. Cambios v2 (radio sincronizado, multi-perro, navegación, matches por perro)

Esta segunda iteración implementa cinco mejoras interrelacionadas:

### 5.1. Radio de búsqueda configurable y sincronizado (Descubrir ⇄ Mapa)

- Nuevo campo `users.radio_busqueda_km` (default 10, máx 50) — migración
  `2026_05_29_000002_multi_perro_y_radio.php`.
- En **Descubrir** y en **Mapa** hay un slider de radio (1–50 km). Al cambiarlo
  en un sitio se persiste en el usuario y al entrar en el otro aparece con el
  mismo valor → totalmente sincronizado.
- En Descubrir, los perros se filtran por distancia real (Haversine) y se
  ordenan por cercanía. Si no hay ubicación, se muestran todos.
- `User::radioBusqueda()` (clamp 1–50, default 10).
- Componentes: `app/Livewire/DiscoverPerros.php`, `app/Livewire/MapaPerros.php`.

### 5.2. Múltiples perros por usuario

- Se permite asociar varios perros a un mismo dueño desde **Editar perfil**.
- El paso 2 del wizard ahora gestiona una **lista de perros** con tarjetas
  individuales (editar / eliminar) y un botón **"Añadir perro"**. Cada perro se
  guarda independientemente con todas sus opciones (raza, edad, energía,
  carácter, foto, etc.).
- El dashboard muestra **todos** tus perros (no solo el primero).
- `app/Livewire/EditarPerfil.php` y su vista, `dashboard.blade.php`,
  `app/Http/Controllers/DashboardController.php`.

### 5.3. Navegación: click en perro → perfil del perro

- Nueva ruta `GET /perro/{perroId}` (`route('ver-perro')`) que muestra el
  perfil **enfocado en ese perro**, ocultando el resto de perros del dueño.
- El componente `VerPerfil` ahora opera en **dos modos**:
  - Modo usuario (`/perfil/{userId}` o `/mi-perfil`): destaca el primer perro y
    lista los demás del dueño en una sección "Sus perros".
  - Modo perro (`/perro/{perroId}`): destaca ese perro y no muestra sus
    hermanos (foco total en el perro elegido).
- En Descubrir y Mapa, la **foto y el nombre** del perro llevan a `ver-perro`.

### 5.4. Navegación: click en usuario → perfil del usuario

- En cualquier punto donde aparezca un dueño (Descubrir, Mapa, Chat,
  Notificaciones, sección "Sus perros" en ver-perfil), un click en su nombre o
  avatar lleva a `route('ver-perfil', $userId)`, donde se ven **todos sus perros**.
- En Notificaciones, los chips de "Tus matches" ya no llevan al chat sino al
  perfil del usuario (consistencia).

### 5.5. Matches por perro, chat por usuario

- Cambio de modelo: el constraint único de `likes` pasa de
  `(de_user_id, a_user_id)` a `(de_user_id, a_perro_id)` → ahora puedes dar
  like a **varios perros del mismo dueño** y se cuentan como likes distintos.
- El **match** sigue siendo entre dos usuarios (reciprocidad), pero se calcula
  agregando los likes per-perro. `Like::darLike($deUser, $aUser, $dePerro, $aPerro)`
  registra el like a un perro concreto y, si hay reciprocidad, marca match en
  todos los likes recíprocos pendientes y crea la conversación.
- El **chat** se mantiene a nivel de usuario (una sola conversación entre dos
  usuarios), pero ahora muestra:
  - Las fotos de **todos los perros con los que has hecho match** con esa
    persona (en cabecera del chat y en el banner "💞 Match con …").
  - Si has matcheado con dos perros del mismo dueño, ambos aparecen en la
    misma ventana de chat.
  - El avatar del **dueño** sigue siendo visible y enlaza a su perfil.
- En las notificaciones se indica **a qué perro tuyo** te ha dado like (no a
  "tu perfil" genérico).
- `Conversacion::perrosMatcheados($userId)` deriva los perros matcheados de la
  tabla likes (no se añade tabla pivote).

### 5.6. Demo en seeders

- El usuario **admin** tiene **dos perros** (Lola + Coco) → demuestra
  multi-perro.
- Uno de los matches del admin es **multi-perro**: la otra persona ha dado
  like a Lola y a Coco, y admin le ha devuelto el like a su perro. En el chat,
  desde el lado de esa persona se ven los dos perros de admin matcheados.
- Hay likes pendientes a distintos perros del admin (a Lola y a Coco) para
  ilustrar el flujo per-perro en notificaciones.

### 5.7. Archivos modificados / añadidos (v2)

- `database/migrations/2026_05_29_000002_multi_perro_y_radio.php` (nuevo)
- `app/Models/User.php` (radio_busqueda_km, perroPrincipal, radioBusqueda)
- `app/Models/Like.php` (darLike reescrito per-perro)
- `app/Models/Conversacion.php` (perrosMatcheados)
- `app/Livewire/VerPerfil.php` + `resources/views/livewire/ver-perfil.blade.php` (modo dual)
- `app/Livewire/DiscoverPerros.php` + su vista (radio sincronizado + links)
- `app/Livewire/MapaPerros.php` (radio sincronizado + dueno_url)
- `app/Livewire/ChatPerros.php` + su vista (multi-perro en cabecera)
- `app/Livewire/EditarPerfil.php` + su vista (gestión multi-perro)
- `app/Livewire/Notificaciones.php` + su vista (per-perro + links a perfil)
- `app/Http/Controllers/DashboardController.php` + `resources/views/dashboard.blade.php`
- `resources/views/partials/sidebar.blade.php` (active state ver-perro)
- `routes/web.php` (ruta ver-perro)
- `database/seeders/PerroSeeder.php` (segundo perro de admin)
- `database/seeders/SocialSeeder.php` (likes per-perro + match multi-perro)

---

## 6. Cambios v3 (fotos de storage, atajo a ubicación)

### 6.1. Las fotos subidas a storage se sirven sin depender de `storage:link`

Las fotos subidas (avatares de usuario, fotos de perros) se guardaban en
`storage/app/public/...` y se exponían como `/storage/...`. Eso requiere que
exista el symlink `public/storage → storage/app/public` (que se crea con
`php artisan storage:link`). Si no existía, las imágenes apuntaban a un path
inexistente y el navegador las trataba como enlaces rotos.

Ahora las imágenes se sirven a través de un controlador propio que lee
directamente del disco `public`, así que **el sistema funciona aunque no se
haya ejecutado `storage:link`**.

- **Nuevo**: `app/Http/Controllers/ImagenController.php` — sirve un archivo del
  disco `public` con `Storage::disk('public')->response(...)`, devuelve 404
  si no existe, incluye cabeceras de cache de 7 días.
- **Nueva ruta**: `GET /img/{ruta}` (`route('imagen.show')`, acepta
  subrutas) — pública (no requiere auth para que las imágenes sean
  cacheables y embebibles sin sesión).
- Modificados `Perro::resolverUrl()`, `User::getAvatarPhotoAttribute()` y
  `EditarPerfil::resolverUrl()` para devolver `url('img/...')` en vez de
  `asset('storage/...')`. Se mantiene compat con valores guardados como
  `/storage/...` (formato heredado) y con URLs absolutas / data URIs.

### 6.2. Atajo en mi perfil al paso de ubicación

- Al lado de "Editar perfil", botón **"Configurar ubicación"** (o "Ubicación"
  si ya está fijada). Lleva a `/perfil?paso=3`, directamente al paso de
  ubicación.
- Si el usuario aún no tiene ubicación, se muestra además un **banner
  destacado** al inicio de su perfil invitando a configurarla.

### 6.3. Atajo desde el mapa al paso de ubicación

- Si entras en `/mapa` sin tener ubicación fijada, ya no se intenta mostrar
  el mapa (Leaflet no tiene un centro válido y se vería raro). En su lugar
  se muestra una **pantalla informativa** con un gran botón
  **"Configurar mi ubicación"** que lleva a `/perfil?paso=3`.
- Cuando la ubicación está fijada, el mapa se carga normalmente.

### 6.4. EditarPerfil admite `?paso=N` por URL

- `EditarPerfil::mount()` ahora respeta `?paso=N` del query string (valores
  válidos: 1, 2, 3). Esto permite enlaces directos a un paso concreto del
  wizard desde cualquier parte de la app.

### 6.5. Archivos modificados / añadidos (v3)

- `app/Http/Controllers/ImagenController.php` (nuevo)
- `routes/web.php` (ruta `imagen.show`)
- `app/Models/Perro.php` (`resolverUrl` por `/img/`)
- `app/Models/User.php` (`getAvatarPhotoAttribute` por `/img/`)
- `app/Livewire/EditarPerfil.php` (`resolverUrl` por `/img/`, `mount` lee `?paso`)
- `app/Livewire/MapaPerros.php` (`tieneUbicacion` al view)
- `resources/views/livewire/ver-perfil.blade.php` (botón + banner ubicación)
- `resources/views/livewire/mapa-perros.blade.php` (pantalla sin-ubicación)

---

## 7. Cambios v4 (fotos en discover, matches por perro en notificaciones, mapa robusto)

### 7.1. Fotos subidas se cargan también en Descubrir

En `discover-perros.blade.php` la `<img src>` estaba usando el campo raw
`$perro->foto_principal` en lugar del accessor `foto_principal_url`. Al
guardarse una foto a mano se almacenaba como `/storage/perros/x.jpg` y
se renderizaba tal cual, sin pasar por el resolver de `/img/...`. Corregido
para que use el accessor — ya carga bien en todas las tarjetas de Descubrir.

(Resto de sitios: dashboard, ver-perfil, editar-perfil, chat ya usaban el
accessor, sin cambios.)

### 7.2. "Tus matches" en notificaciones: ahora son perros, no usuarios

Antes la sección listaba `likesRecibidos()->whereNotNull('match_at')`, que
con el modelo per-perro genera un like por **cada perro que recibió like**.
Resultado: si dos personas hacían match contigo en dos perros distintos,
salían chips repetidos.

Ahora los chips muestran los **perros con los que has hecho match**
(los `aPerro` de tus likes correspondidos, deduplicados por
`a_perro_id`). Cada chip muestra la foto del perro, su nombre y el del
dueño, y enlaza a `route('ver-perro', $perro->id)`.

- `app/Livewire/Notificaciones.php` — query reescrita.
- `resources/views/livewire/notificaciones.blade.php` — chips reescritos.

### 7.3. Mapa de "Ubicación" no se queda en blanco al hacer click

El mini-mapa del paso 3 de Editar perfil se quedaba en blanco al hacer
click. Eran tres problemas combinados:

1. **Re-render de Livewire**: al hacer click el componente llamaba
   `$wire.setUbicacionDesdeJS(...)`, que actualiza `$latitud/$longitud` en
   PHP y dispara un re-render. Livewire hacía morph del subárbol y rompía
   el estado interno de Leaflet y de Alpine.
2. **Carga del script de Leaflet**: el `<script src="leaflet.js">` estaba
   dentro del propio componente, y Alpine intentaba `initMap()` antes de
   que `window.L` estuviera definido.
3. **Tamaño inicial**: a veces el contenedor calculaba alto cero al abrirse.

Solución aplicada:

- **`wire:ignore`** en el `<div>` del mapa para que Livewire no toque su
  subárbol al re-renderizar — Leaflet conserva su estado.
- **`wire:ignore.self`** en el contenedor Alpine para evitar que se
  reinicialice por cambios de atributo.
- **Carga dinámica de Leaflet** desde el propio Alpine: si `window.L`
  no existe, se inyecta el CSS y el JS en `<head>` con un id único, se
  espera al `onload` y entonces se llama `initMap()`. Idempotente.
- **`map.invalidateSize()`** diferido para recalcular dimensiones si el
  contenedor era inicialmente invisible.

Los demás controles del paso 3 (badge "Ubicación fijada", toggle de
tiempo real, botones) siguen reaccionando con normalidad porque están
fuera del bloque `wire:ignore`.

### 7.4. Archivos modificados (v4)

- `resources/views/livewire/discover-perros.blade.php` (img src usa accessor)
- `app/Livewire/Notificaciones.php` (matches per-perro)
- `resources/views/livewire/notificaciones.blade.php` (chips de perros)
- `resources/views/livewire/editar-perfil.blade.php` (paso 3: wire:ignore + carga dinámica Leaflet)

## 8. Sistema Premium (freemium) + revisión y corrección de bugs (v5)

### 8.1. Modelo de negocio freemium — límite de 3 matches

- El **plan gratuito permite 3 matches activos a la vez**; el **plan premium**
  los hace **ilimitados**. Es la palanca de conversión: cuando un usuario
  gratuito ya tiene 3 conversaciones y va a cerrar un cuarto match, se le
  bloquea y se le ofrece premium.
- Lógica en `App\Models\User`:
  - `LIMITE_MATCHES_GRATIS = 3`
  - `matchesActivos()` — nº de conversaciones (cada match crea una).
  - `puedeIniciarMatch()` — `true` si es premium o tiene < 3 matches.
  - `matchesRestantes()` — los que le quedan (null si premium).
- `App\Models\Like::seriaMatch($de, $a)` detecta si un like **cerraría** un
  match (existe like recíproco), para aplicar el límite **antes** de crearlo.
- El control se aplica en los **tres** puntos donde se cierra un match:
  `DiscoverPerros::darLike`, `Notificaciones::corresponder` y
  `VerPerfil::darLikePerro`. Si se bloquea, se muestra un aviso con enlace a
  premium (no se pierde el like pendiente del otro usuario).
- **Página de suscripción** funcional en `/premium`:
  - `app/Livewire/Premium.php` + `resources/views/livewire/premium.blade.php`.
  - Muestra el plan actual, el uso de matches y las ventajas; botón
    *"Hazte Premium"* que activa el plan (demo, sin pasarela: fija
    `plan = premium`, `plan_expira_at = +1 mes` y asigna el rol `premium`).
    Botón para volver al plan gratuito.
- UI: entrada **Premium** en el menú lateral y tarjeta con los *matches
  restantes* para usuarios gratuitos (`partials/sidebar.blade.php`).

> Nota: el límite se aplica al usuario que **realiza** la acción de cerrar el
> match (dar like de vuelta / dar el like que produce reciprocidad).

### 8.2. Bugs corregidos

1. **Fuga de privacidad en el mini-mapa del Dashboard.** `DashboardController`
   enviaba al navegador las **coordenadas reales** de otros usuarios. Ahora usa
   `User::coordenadasFuzzificadas()`, igual que el mapa principal.
2. **Imágenes de perro rotas.** Las semillas/factory usaban `place.dog`
   (servicio caído) y el *fallback* del modelo apuntaba a un endpoint de
   `dog.ceo` que devuelve **JSON**, no una imagen. Se sustituye por
   **placeholders SVG locales** (`public/img/perros/ph-1..6.svg`), elegidos de
   forma determinística por `id`. Funcionan **sin conexión** (p. ej. en la
   máquina virtual de entrega). Ver `Perro::getFotoUrlAttribute()`.
3. **Ficheros huérfanos al cambiar foto.** En `EditarPerfil`, al sustituir el
   avatar o la foto de un perro no se borraba el fichero anterior (se comparaba
   contra una URL ya resuelta). Ahora se borra usando el valor almacenado real.
4. **Factory: usuarios premium sin fecha de expiración.** `UserFactory` ponía
   `plan = premium` sin `plan_expira_at`, por lo que `es_premium` daba `false`.
   Ahora fija una fecha futura coherente.

### 8.3. Archivos añadidos / modificados (v5)

- `app/Models/User.php` (lógica freemium)
- `app/Models/Like.php` (`seriaMatch()`)
- `app/Models/Perro.php` (placeholder local; comentarios)
- `app/Livewire/Premium.php` **(nuevo)** + `resources/views/livewire/premium.blade.php` **(nuevo)**
- `app/Livewire/DiscoverPerros.php`, `Notificaciones.php`, `VerPerfil.php` (gate de matches)
- `app/Livewire/EditarPerfil.php` (borrado de ficheros anteriores)
- `app/Http/Controllers/DashboardController.php` (coordenadas difuminadas)
- `database/factories/UserFactory.php`, `PerroFactory.php`, `database/seeders/PerroSeeder.php`
- `resources/views/partials/sidebar.blade.php` (menú Premium + matches restantes)
- `resources/views/livewire/{discover-perros,notificaciones,ver-perfil}.blade.php` (aviso premium)
- `public/img/perros/ph-1..6.svg` **(nuevos placeholders)**
- `routes/web.php` (ruta `/premium`)

## 9. Ajustes Premium + ubicación en tiempo real (v6)

### 9.1. Switch de ubicación en tiempo real accesible

- El interruptor existía solo en el paso 3 del onboarding. Ahora hay un
  **switch visible directamente en "Mi perfil"**: `VerPerfil::toggleUbicacionTiempoReal()`
  + tarjeta con el estado en `resources/views/livewire/ver-perfil.blade.php`.
- No se puede activar sin una ubicación fijada (aviso explicativo).

### 9.2. Notificaciones gratuitas = solo mensajes nuevos

- Coherencia con el premium "ver quién te ha dado like": en el plan **gratuito**
  las notificaciones muestran **solo mensajes nuevos** (no leídos); los likes
  recibidos quedan ocultos tras un gancho ("Tienes N likes esperando").
- El plan **premium** sí ve el detalle de los likes y puede corresponder.
- Nuevos helpers en `User`: `mensajesNoLeidos()` y
  `conversacionesConMensajesNuevos()`.
- `Notificaciones` (componente + vista) reescritos. El badge del menú lateral
  cuenta mensajes nuevos para todos y, además, likes pendientes solo si es premium.

### 9.3. Distintivo Premium claro

- Nuevo componente reutilizable `resources/views/components/premium-badge.blade.php`
  (`<x-premium-badge />`, con tamaños `sm`/`md`): píldora con estrella y la
  palabra "Premium" en la paleta del proyecto.
- Sustituye al discreto ✨ en: perfil (cabecera y bloque del dueño), tarjetas de
  Descubrir, lista y cabecera del Chat, y sugerencias del Dashboard.

### 9.4. Radio del plan gratuito limitado a 15 km

- Tope centralizado en `User`: `RADIO_MAX_GRATIS = 15`, `RADIO_MAX_PREMIUM = 50`,
  `radioMaximo()`. `radioBusqueda()` acota el valor leído al máximo del plan.
- Aplicado al **guardar** y al **filtrar** en `DiscoverPerros` y `MapaPerros`.
- Sliders dinámicos (`max` = tope del plan) con gancho premium en ambas vistas.
- Al cancelar premium se recorta el radio guardado a 15 km (`Premium::cancelar`).

### 9.5. Archivos añadidos / modificados (v6)

- `app/Models/User.php` (radio por plan, mensajes no leídos)
- `app/Livewire/VerPerfil.php` (toggle tiempo real)
- `app/Livewire/Notificaciones.php` (mensajes vs likes según plan)
- `app/Livewire/ChatPerros.php` (flag premium del interlocutor)
- `app/Livewire/DiscoverPerros.php`, `MapaPerros.php` (tope de radio dinámico)
- `app/Livewire/Premium.php` (recorte de radio al bajar de plan)
- `resources/views/components/premium-badge.blade.php` **(nuevo)**
- `resources/views/livewire/{ver-perfil,notificaciones,chat-perros,discover-perros,mapa-perros}.blade.php`
- `resources/views/dashboard.blade.php`, `resources/views/partials/sidebar.blade.php`

## 10. Match a nivel de usuario, pantalla de match, parques reales, validación (v7)

### 10.1. Match = pareja de usuarios (no de perros)

- Cuando hay match con alguien, **se cierra match automáticamente con todos
  sus perros** (y los míos con los suyos). Antes era por perro.
- En el chat aparecen todos los perros de ambos lados.
- En perfiles ajenos con match, no se puede dar like a otros perros suyos
  (ya están todos matcheados).
- Implementado en `Like::darLike()` con un nuevo helper privado
  `completarLikesPara()` que rellena los likes faltantes.

### 10.2. Pantalla de match (modal)

- Nuevo componente Livewire global `MatchModal` (montado en el layout y
  escuchando el evento `match-cerrado`).
- Modal de celebración con confeti CSS, avatares de ambas personas, tarjetas
  con los perros de cada lado y CTA "Enviar mensaje" / "Seguir descubriendo".
- Disparado desde `DiscoverPerros::darLike`, `VerPerfil::darLikePerro` y
  `Notificaciones::corresponder`.
- Archivos: `app/Livewire/MatchModal.php`,
  `resources/views/livewire/match-modal.blade.php`,
  inclusión global en `resources/views/layouts/app.blade.php`.

### 10.3. Descubrir: like y "pasar" desaparecen el perro de verdad

- Al dar like a un perro o "pasarlo", desaparece del feed.
- "Pasar" ahora es **persistente** (nueva tabla `perros_pasados`):
  `database/migrations/2026_06_01_000001_create_perros_pasados_table.php`.
- El filtro del feed excluye: perros ya likeados (pendientes o con match),
  usuarios con los que ya hay match, y perros pasados.

### 10.4. Capas del mapa funcionan

- Bug previo: `pintar()` siempre repintaba ambas capas con los datos
  recibidos, y al desactivar una capa el componente devolvía el array vacío;
  pero las claves del JSON podían llegar como objeto en lugar de array y el
  listener `mapa-datos` podía no registrarse a tiempo.
- Solución: en `MapaPerros::mapData()` se fuerza `->values()->all()` (array
  indexado) y se incluyen flags `capas` en el payload.
- `public/js/pawmap.js` reescrito con registro idempotente y resistente del
  listener `mapa-datos` (también si el JS se carga después de `livewire:init`),
  y respeta `capas.perros` / `capas.parques`.

### 10.5. Parques reales (no hardcodeados)

- `MapaPerros::parques()` ahora consulta la API **Overpass de OpenStreetMap**
  (gratuita, sin clave) por parques caninos (`leisure=dog_park`) dentro del
  bounding box derivado del radio de búsqueda del usuario.
- Resultado cacheado 12 horas por celda geográfica para no sobrecargar el
  servicio; si Overpass falla, la app sigue funcionando con la capa vacía.
- Imports añadidos: `Http`, `Cache`.

### 10.6. Errores de validación legibles (en español)

- Locale por defecto a `es` en `config/app.php`.
- Nuevas traducciones:
  - `lang/es/validation.php` (mensajes + atributos legibles: "El campo
    nombre es obligatorio", "El campo peso (kg) debe ser al menos 0.1", …).
  - `lang/es/auth.php`, `lang/es/passwords.php`, `lang/es/pagination.php`.
- Mensajes personalizados para `perroPesoKg`, `perroEdadAnios`, etc.

### 10.7. No se pueden registrar perros con edad y peso 0

- `EditarPerfil::reglasPerro()`: `perroPesoKg` ahora es `required` con
  `min:0.1`.
- `EditarPerfil::guardarPerro()`: validación cruzada — años y meses no
  pueden ser ambos 0 (mensaje: "Indica al menos un mes o un año de edad.").

### 10.8. Archivos añadidos / modificados (v7)

- `app/Models/Like.php` (cascade-match)
- `app/Livewire/MatchModal.php` **(nuevo)** +
  `resources/views/livewire/match-modal.blade.php` **(nuevo)**
- `app/Livewire/DiscoverPerros.php` (pasar persistente, modal dispatch)
- `app/Livewire/VerPerfil.php` (bloqueo like si ya match; modal dispatch)
- `app/Livewire/Notificaciones.php` (modal dispatch)
- `app/Livewire/MapaPerros.php` (parques reales + capas)
- `app/Livewire/EditarPerfil.php` (validación peso/edad)
- `public/js/pawmap.js` (listener idempotente + capas)
- `database/migrations/2026_06_01_000001_create_perros_pasados_table.php` **(nueva)**
- `lang/es/{validation,auth,passwords,pagination}.php` **(nuevas)**
- `config/app.php` (locale `es` por defecto)
- `resources/views/layouts/app.blade.php` (modal global)
