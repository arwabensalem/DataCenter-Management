/**
 * GreenDC Advisor — scripts globaux
 */
document.addEventListener('DOMContentLoaded', () => {
    // Fermeture automatique des alertes après 5s
    document.querySelectorAll('.alert-dismissible').forEach((alertEl) => {
        setTimeout(() => {
            const instance = bootstrap.Alert.getOrCreateInstance(alertEl);
            instance.close();
        }, 5000);
    });

    // Aperçu live des consommations (formulaire équipement)
    const form = document.getElementById('equipement-form');
    if (form) {
        const inputs = form.querySelectorAll('.calc-input');
        const elJour = document.getElementById('preview-jour');
        const elMois = document.getElementById('preview-mois');
        const elAn = document.getElementById('preview-an');

        const parseNum = (v) => {
            const n = parseFloat(String(v).replace(',', '.'));
            return Number.isFinite(n) ? n : null;
        };

        const fmt = (n) => n.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const updatePreview = () => {
            const q = parseNum(form.quantite.value);
            const p = parseNum(form.puissance_watts.value);
            const t = parseNum(form.taux_utilisation.value);
            const h = parseNum(form.heures_fonctionnement.value);

            if (q === null || p === null || t === null || h === null || q < 1 || p <= 0 || t < 0 || t > 100 || h <= 0 || h > 24) {
                elJour.textContent = '— kWh';
                elMois.textContent = '— kWh';
                elAn.textContent = '— kWh';
                return;
            }

            const jour = (p * (t / 100) * q * h) / 1000;
            elJour.textContent = fmt(jour) + ' kWh';
            elMois.textContent = fmt(jour * 30) + ' kWh';
            elAn.textContent = fmt(jour * 365) + ' kWh';
        };

        inputs.forEach((input) => input.addEventListener('input', updatePreview));
        updatePreview();
    }

    // Aperçu live dimensionnement photovoltaïque
    const pvForm = document.getElementById('pv-form');
    if (pvForm && window.GDC_PV_PANNEAUX) {
        const parseNum = (v) => {
            const n = parseFloat(String(v).replace(',', '.'));
            return Number.isFinite(n) ? n : null;
        };
        const fmt = (n, d = 1) => n.toLocaleString('fr-FR', { maximumFractionDigits: d, minimumFractionDigits: 0 });
        const set = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };

        const typeSelect = document.getElementById('type_panneau_id');
        const heuresInput = document.getElementById('heures_ensoleillement');
        const regionSelect = document.getElementById('region_ville');
        const details = document.getElementById('panneau-details');
        const warning = document.getElementById('pv-warning');

        const conso = parseFloat(pvForm.dataset.conso);
        const surface = parseFloat(pvForm.dataset.surface);
        const prixKwh = parseFloat(pvForm.dataset.prixKwh);
        const co2Factor = parseFloat(pvForm.dataset.co2 || '0.55');

        if (regionSelect) {
            regionSelect.addEventListener('change', () => {
                if (regionSelect.value) {
                    heuresInput.value = regionSelect.value;
                    updatePv();
                }
            });
        }

        const updatePv = () => {
            const type = window.GDC_PV_PANNEAUX[typeSelect.value];
            const heures = parseNum(heuresInput.value);

            if (!type || heures === null || heures <= 0 || heures > 24) {
                ['pv-nb','pv-kwc','pv-surf','pv-prod','pv-couv','pv-steg','pv-cout','pv-roi','pv-amort']
                    .forEach((id) => set(id, '—'));
                details.classList.add('d-none');
                warning.classList.add('d-none');
                return;
            }

            details.classList.remove('d-none');
            details.innerHTML = `<strong>${type.nom}</strong> — ${fmt(type.puissance_wc, 0)} Wc ·
                rendement ${fmt(type.rendement, 1)} % · ${fmt(type.surface_m2, 2)} m² ·
                ${fmt(type.prix_unitaire, 0)} TND / panneau`;

            const prodPanneau = (type.puissance_wc * heures * 365 * type.rendement) / 100000;
            const panneauxNec = prodPanneau > 0 ? Math.ceil(conso / prodPanneau) : 0;
            const kwc = (panneauxNec * type.puissance_wc) / 1000;
            const maxSurf = type.surface_m2 > 0 ? Math.floor(surface / type.surface_m2) : 0;
            const nb = Math.max(0, Math.min(panneauxNec, maxSurf));
            const surfNec = nb * type.surface_m2;
            const production = nb * prodPanneau;
            const couverture = conso > 0 ? Math.min(100, (production / conso) * 100) : 0;
            const energiePv = Math.min(production, conso);
            const energieSteg = Math.max(0, conso - energiePv);
            const cout = nb * type.prix_unitaire;
            const economie = energiePv * prixKwh;
            const roi = cout > 0 ? (economie / cout) * 100 : 0;
            const amort = economie > 0 ? cout / economie : 0;

            set('pv-nb', String(nb));
            set('pv-kwc', fmt(kwc, 2) + ' kWc');
            set('pv-surf', fmt(surfNec, 1) + ' m²');
            set('pv-prod', fmt(production, 0) + ' kWh');
            set('pv-couv', fmt(couverture, 1) + ' %');
            set('pv-steg', fmt(energieSteg, 0) + ' kWh');
            set('pv-cout', fmt(cout, 0) + ' TND');
            set('pv-roi', fmt(roi, 1) + ' %');
            set('pv-amort', amort > 0 ? fmt(amort, 1) + ' ans' : '—');

            if (panneauxNec > maxSurf) {
                warning.classList.remove('d-none');
                warning.textContent = `Surface insuffisante pour 100 % de couverture : ${panneauxNec} panneaux nécessaires, ${maxSurf} installables.`;
            } else {
                warning.classList.add('d-none');
            }
        };

        typeSelect.addEventListener('change', updatePv);
        heuresInput.addEventListener('input', updatePv);
        updatePv();
    }
});
