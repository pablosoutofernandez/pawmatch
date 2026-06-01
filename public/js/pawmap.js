// PawMatch — lógica del mapa (Leaflet). Externalizado para no romper la
// detección de "raíz única" de Livewire (DOMDocument mal-parsea los <tags>
// que van dentro de los template strings de los popups si están inline).
(function () {
    'use strict';

    let map = null, userMarker = null, perrosLayer = null, parquesLayer = null, centradoUna = false;

    function cargarCss() {
        if (!document.getElementById('leaflet-css')) {
            const l = document.createElement('link');
            l.id = 'leaflet-css'; l.rel = 'stylesheet';
            l.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(l);
        }
        if (!document.getElementById('paw-map-style')) {
            const s = document.createElement('style');
            s.id = 'paw-map-style';
            s.textContent = '@keyframes pawpulse{0%{transform:scale(.6);opacity:.8}100%{transform:scale(1.6);opacity:0}}';
            document.head.appendChild(s);
        }
    }

    const iconoCompat = (c) => L.divIcon({ className: '', iconSize: [42, 42], iconAnchor: [21, 21],
        html: '<div style="background:#f02d5e;color:#fff;border-radius:50%;width:42px;height:42px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;box-shadow:0 4px 14px rgba(240,45,94,0.4);border:2px solid #fff;cursor:pointer">' + c + '%</div>' });

    const iconoParque = () => L.divIcon({ className: '', iconSize: [30, 30], iconAnchor: [15, 15],
        html: '<div style="background:#16a34a;color:#fff;border-radius:12px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-size:15px;box-shadow:0 3px 10px rgba(22,163,74,0.4);border:2px solid #fff">🌳</div>' });

    const iconoUsuario = () => L.divIcon({ className: '', iconSize: [22, 22], iconAnchor: [11, 11],
        html: '<div style="position:relative;width:22px;height:22px"><div style="position:absolute;inset:0;background:#2563eb;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(37,99,235,.5)"></div><div style="position:absolute;inset:-8px;background:rgba(37,99,235,.18);border-radius:50%;animation:pawpulse 2s infinite"></div></div>' });

    function popupPerro(d) {
        return '<strong>' + d.nombre + '</strong><br><span style="color:#737373">' + d.raza + '</span><br>'
             + '<strong style="color:#f02d5e">' + d.compat + '% compatible</strong> · ' + d.distancia + '<br>'
             + '<a href="' + d.perfil_url + '" style="color:#2563eb;font-weight:600">Ver perfil →</a>';
    }
    function popupParque(p) {
        return '<strong>' + p.nombre + '</strong><br><span style="color:#737373">' + p.tipo + '</span>';
    }

    function pintar(data) {
        if (!map || !data) return;
        const u = data.usuario;
        const aviso = document.getElementById('aviso-ubicacion');
        if (aviso) aviso.classList.toggle('hidden', !!u.tiene);

        if (!userMarker) {
            userMarker = L.marker([u.lat, u.lng], { icon: iconoUsuario(), zIndexOffset: 1000 })
                          .addTo(map).bindPopup('📍 Tú estás aquí');
        } else {
            userMarker.setLatLng([u.lat, u.lng]);
        }

        // Flags de capas (si no vienen, asumimos visibles para no romper).
        const capas = data.capas || { perros: true, parques: true };

        // ── Perros: limpiar y volver a pintar SOLO si la capa está activa.
        if (perrosLayer) { perrosLayer.remove(); perrosLayer = null; }
        if (capas.perros) {
            perrosLayer = L.layerGroup();
            (data.perros || []).forEach(function (d) {
                L.marker([d.lat, d.lng], { icon: iconoCompat(d.compat) }).bindPopup(popupPerro(d)).addTo(perrosLayer);
            });
            perrosLayer.addTo(map);
        }

        // ── Parques: idem.
        if (parquesLayer) { parquesLayer.remove(); parquesLayer = null; }
        if (capas.parques) {
            parquesLayer = L.layerGroup();
            (data.parques || []).forEach(function (p) {
                L.marker([p.lat, p.lng], { icon: iconoParque() }).bindPopup(popupParque(p)).addTo(parquesLayer);
            });
            parquesLayer.addTo(map);
        }

        if (!centradoUna) { map.setView([u.lat, u.lng], 14); centradoUna = true; }
    }
    window.__pawRepaint = pintar;

    function initMap() {
        const el = document.getElementById('map');
        if (!el || el._initialized || typeof L === 'undefined' || !window.__pawMapData) return;
        el._initialized = true;

        const d = window.__pawMapData;
        map = L.map('map', { zoomControl: false }).setView([d.usuario.lat, d.usuario.lng], 14);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
            { attribution: '&copy; OSM &copy; CARTO', subdomains: 'abcd', maxZoom: 20 }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);
        pintar(d);
    }

    // Punto de entrada llamado desde el Blade (idempotente)
    window.pawMapInit = function () {
        cargarCss();
        (function esperar(n) {
            if (typeof L !== 'undefined' && document.getElementById('map')) initMap();
            else if (n > 0) setTimeout(function () { esperar(n - 1); }, 100);
        })(40);
    };

    // ── Listener del evento Livewire 'mapa-datos': se registra de forma
    //    resistente a que el JS llegue antes o después de Livewire.
    function registrarListener() {
        if (!window.Livewire || window.__pawListenerRegistrado) return;
        window.__pawListenerRegistrado = true;
        window.Livewire.on('mapa-datos', function (payload) {
            // Livewire 3 puede entregar el payload como objeto {data:...} o
            // como array [{data:...}] según la versión.
            let data;
            if (Array.isArray(payload)) {
                data = payload[0]?.data ?? payload[0];
            } else if (payload && payload.data !== undefined) {
                data = payload.data;
            } else {
                data = payload;
            }
            window.__pawMapData = data;
            pintar(data);
        });
    }

    // Registrar listeners una sola vez
    if (!window.__pawMapListeners) {
        window.__pawMapListeners = true;

        // En navegación SPA el #map es nuevo: reiniciar estado del mapa
        document.addEventListener('livewire:navigated', function () {
            map = null; userMarker = null; perrosLayer = null; parquesLayer = null; centradoUna = false;
            registrarListener();
            if (window.pawMapInit) window.pawMapInit();
        });

        // Livewire 3 emite 'livewire:init' al cargar; pero si pawmap.js se
        // carga DESPUÉS de ese evento, no se dispara. Por eso intentamos
        // registrar también de forma directa si window.Livewire ya existe.
        document.addEventListener('livewire:init', registrarListener);
        if (window.Livewire) registrarListener();
        // Reintento por si Livewire aparece más tarde
        setTimeout(registrarListener, 300);
        setTimeout(registrarListener, 1500);

        // Ubicación en tiempo real del navegador (emitida por partials/geolocate)
        window.addEventListener('ubicacion-actualizada', function (ev) {
            if (!window.__pawMapData) return;
            window.__pawMapData.usuario.lat = ev.detail.lat;
            window.__pawMapData.usuario.lng = ev.detail.lng;
            window.__pawMapData.usuario.tiene = true;
            pintar(window.__pawMapData);
        });
    }
})();
