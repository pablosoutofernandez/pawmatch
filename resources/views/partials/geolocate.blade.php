{{-- Captura la ubicación del navegador y la envía al servidor.
     - Pide permiso al cargar (clave para calcular cercanía).
     - watchPosition: actualiza en tiempo real mientras la pestaña está abierta.
     - Reemite el evento Livewire/JS 'ubicacion-actualizada' para que el mapa
       mueva el marcador "Tú" sin recargar. --}}
<script>
(function () {
    if (window.__pawGeoInit) return;
    window.__pawGeoInit = true;

    const ENDPOINT = "{{ route('ubicacion.actualizar') }}";
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    let ultimaSubida = 0;
    const MIN_INTERVALO = 15000; // como mucho una subida al servidor cada 15 s

    function enviar(lat, lng, forzar = false) {
        const ahora = Date.now();
        // Avisar siempre al mapa (es instantáneo y local)
        window.dispatchEvent(new CustomEvent('ubicacion-actualizada', {
            detail: { lat: lat, lng: lng }
        }));

        if (!forzar && ahora - ultimaSubida < MIN_INTERVALO) return;
        ultimaSubida = ahora;

        fetch(ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ latitud: lat, longitud: lng }),
        }).catch(() => { /* silencioso */ });
    }

    function iniciar() {
        if (!('geolocation' in navigator)) return;

        // Captura inmediata (una vez)
        navigator.geolocation.getCurrentPosition(
            (pos) => enviar(pos.coords.latitude, pos.coords.longitude, true),
            () => { /* permiso denegado: se usa la última ubicación guardada */ },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
        );

        // Seguimiento en tiempo real
        navigator.geolocation.watchPosition(
            (pos) => enviar(pos.coords.latitude, pos.coords.longitude),
            () => {},
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 30000 }
        );
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        iniciar();
    } else {
        document.addEventListener('DOMContentLoaded', iniciar);
    }
})();
</script>
