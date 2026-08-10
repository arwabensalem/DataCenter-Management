<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$potentielLabel = static fn(string $p): string => match ($p) {
    'excellent' => 'Excellent',
    'eleve' => 'Élevé',
    'moyen' => 'Moyen',
    default => 'Faible',
};
$potentielClass = static fn(string $p): string => match ($p) {
    'excellent' => 'bg-success',
    'eleve' => 'bg-primary',
    'moyen' => 'bg-warning text-dark',
    default => 'bg-secondary',
};
?>
<div class="mb-4">
    <p class="text-muted mb-0">
        Sélectionnez un gouvernorat pour préremplir automatiquement l'irradiation et les heures d'ensoleillement
        dans le dimensionnement photovoltaïque.
    </p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="form-card">
            <div id="tunisia-map" style="height: 520px; border-radius: 12px;"></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="form-card" id="gov-detail">
            <h2 class="h6 text-uppercase text-muted mb-3">Détail du gouvernorat</h2>
            <p class="text-muted small" id="gov-placeholder">Cliquez sur un marqueur de la carte.</p>
            <div id="gov-content" class="d-none">
                <h3 class="h5" id="gov-nom"></h3>
                <dl class="row detail-list mb-3">
                    <dt class="col-7">Irradiation</dt>
                    <dd class="col-5" id="gov-irr"></dd>
                    <dt class="col-7">Ensoleillement</dt>
                    <dd class="col-5" id="gov-heures"></dd>
                    <dt class="col-7">Potentiel PV</dt>
                    <dd class="col-5" id="gov-pot"></dd>
                    <dt class="col-7">Température moy.</dt>
                    <dd class="col-5" id="gov-temp"></dd>
                </dl>
                <a id="gov-link-dc" href="<?= Security::e(Url::to('data-centers/create')) ?>" class="btn btn-brand btn-sm">
                    <i class="fa-solid fa-server me-1"></i> Créer un Data Center ici
                </a>
            </div>
        </div>

        <div class="table-card mt-3" style="max-height: 280px; overflow: auto;">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Gouvernorat</th>
                        <th>h/j</th>
                        <th>kWh/m²</th>
                        <th>Potentiel</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($gouvernorats as $g): ?>
                    <tr class="gov-row" style="cursor:pointer"
                        data-id="<?= (int) $g['id'] ?>"
                        data-lat="<?= Security::e((string) $g['latitude']) ?>"
                        data-lng="<?= Security::e((string) $g['longitude']) ?>">
                        <td><?= Security::e($g['nom']) ?></td>
                        <td><?= Security::e(number_format((float) $g['heures_ensoleillement'], 1, ',', '')) ?></td>
                        <td><?= Security::e(number_format((float) $g['irradiation_kwh_m2_an'], 0, ',', ' ')) ?></td>
                        <td><span class="badge <?= $potentielClass($g['potentiel_pv']) ?>"><?= Security::e($potentielLabel($g['potentiel_pv'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
window.GDC_MAP_DATA = <?= json_encode($mapData, JSON_UNESCAPED_UNICODE) ?>;
window.GDC_BASE = <?= json_encode(Url::base()) ?>;
</script>
<script src="<?= Security::e(Url::base()) ?>/assets/js/carte.js"></script>
