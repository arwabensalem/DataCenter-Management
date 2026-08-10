/**
 * Carte énergétique Tunisie (Leaflet)
 */
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('tunisia-map');
    if (!el || typeof L === 'undefined' || !window.GDC_MAP_DATA) return;

    const map = L.map('tunisia-map').setView([34.0, 9.5], 6.2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 12,
    }).addTo(map);

    const colorByPotentiel = (p) => {
        if (p === 'excellent') return '#1b7a4e';
        if (p === 'eleve') return '#1a5f8a';
        if (p === 'moyen') return '#f59e0b';
        return '#94a3b8';
    };

    const markers = {};
    const showGov = (g) => {
        document.getElementById('gov-placeholder').classList.add('d-none');
        document.getElementById('gov-content').classList.remove('d-none');
        document.getElementById('gov-nom').textContent = g.nom;
        document.getElementById('gov-irr').textContent = g.irradiation.toLocaleString('fr-FR') + ' kWh/m²/an';
        document.getElementById('gov-heures').textContent = g.heures.toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' h/j';
        document.getElementById('gov-pot').textContent = g.potentiel;
        document.getElementById('gov-temp').textContent = g.temperature != null
            ? g.temperature.toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' °C'
            : '—';
        const link = document.getElementById('gov-link-dc');
        if (link) link.href = window.GDC_BASE + '/data-centers/create?gouvernorat_id=' + g.id;
    };

    window.GDC_MAP_DATA.forEach((g) => {
        const marker = L.circleMarker([g.lat, g.lng], {
            radius: 9,
            fillColor: colorByPotentiel(g.potentiel),
            color: '#fff',
            weight: 2,
            fillOpacity: 0.9,
        }).addTo(map);

        marker.bindPopup(
            `<strong>${g.nom}</strong><br>` +
            `${g.heures} h/j · ${g.irradiation} kWh/m²/an<br>` +
            `Potentiel : ${g.potentiel}`
        );
        marker.on('click', () => showGov(g));
        markers[g.id] = marker;
    });

    document.querySelectorAll('.gov-row').forEach((row) => {
        row.addEventListener('click', () => {
            const id = parseInt(row.dataset.id, 10);
            const g = window.GDC_MAP_DATA.find((x) => x.id === id);
            if (!g) return;
            showGov(g);
            map.setView([g.lat, g.lng], 8);
            if (markers[id]) markers[id].openPopup();
        });
    });
});
