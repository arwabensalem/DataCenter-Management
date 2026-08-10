<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$old = $old ?? [];
$errors = $errors ?? [];
$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');

$typeSelected = (string) ($old['type_panneau_id'] ?? ($existing['type_panneau_id'] ?? ''));
$heuresVal = (string) ($old['heures_ensoleillement'] ?? $heuresDefaut);

// Données panneaux pour JS
$panneauxJson = [];
foreach ($typesPanneaux as $tp) {
    $panneauxJson[(int) $tp['id']] = [
        'nom' => $tp['nom'],
        'puissance_wc' => (float) $tp['puissance_wc'],
        'rendement' => (float) $tp['rendement'],
        'prix_unitaire' => (float) $tp['prix_unitaire'],
        'surface_m2' => (float) $tp['surface_m2'],
    ];
}
$regionsJson = [];
foreach ($regions as $r) {
    $regionsJson[] = [
        'ville' => $r['ville'],
        'heures' => (float) $r['heures_ensoleillement'],
    ];
}
?>
<div class="mb-3">
    <a href="<?= Security::e(Url::to('photovoltaique')) ?>" class="text-decoration-none text-muted">
        <i class="fa-solid fa-arrow-left me-1"></i> Photovoltaïque
    </a>
</div>

<div class="row g-4 mb-2">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-bolt"></i></div>
            <div>
                <div class="stat-label">Conso. annuelle DC</div>
                <div class="stat-value"><?= Security::e($fmt($consoAnnuelle, 0)) ?> <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-ruler-combined"></i></div>
            <div>
                <div class="stat-label">Surface PV dispo.</div>
                <div class="stat-value"><?= Security::e($fmt((float) $dataCenter['surface_disponible_pv'], 0)) ?> <small class="fs-6">m²</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-coins"></i></div>
            <div>
                <div class="stat-label">Prix kWh STEG</div>
                <div class="stat-value"><?= Security::e($fmt((float) $dataCenter['prix_kwh_steg'], 3)) ?> <small class="fs-6">TND</small></div>
            </div>
        </div>
    </div>
</div>

<form method="post"
      action="<?= Security::e(Url::to('photovoltaique/calculer/' . $dataCenter['id'])) ?>"
      class="row g-4" id="pv-form" novalidate
      data-conso="<?= Security::e((string) $consoAnnuelle) ?>"
      data-surface="<?= Security::e((string) $dataCenter['surface_disponible_pv']) ?>"
      data-prix-kwh="<?= Security::e((string) $dataCenter['prix_kwh_steg']) ?>"
      data-co2="0.55">
    <?= Security::csrfField() ?>

    <div class="col-lg-7">
        <div class="form-card">
            <h2 class="h6 text-uppercase text-muted mb-3">
                <i class="fa-solid fa-solar-panel me-1"></i>
                Paramètres — <?= Security::e($dataCenter['nom']) ?>
            </h2>

            <div class="mb-3">
                <label class="form-label" for="type_panneau_id">Type de panneau *</label>
                <select id="type_panneau_id" name="type_panneau_id"
                        class="form-select <?= isset($errors['type_panneau_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach ($typesPanneaux as $tp): ?>
                        <option value="<?= (int) $tp['id'] ?>"
                            <?= (string) $tp['id'] === $typeSelected ? 'selected' : '' ?>>
                            <?= Security::e($tp['nom']) ?>
                            — <?= Security::e($fmt((float) $tp['puissance_wc'], 0)) ?> Wc
                            — <?= Security::e($fmt((float) $tp['prix_unitaire'], 0)) ?> TND
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['type_panneau_id'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['type_panneau_id']) ?></div>
                <?php endif; ?>
            </div>

            <div id="panneau-details" class="alert alert-light border small mb-3 d-none"></div>

            <div class="mb-3">
                <label class="form-label" for="region_ville">Ville / région (ensoleillement)</label>
                <select id="region_ville" class="form-select mb-2">
                    <option value="">— Choisir une ville —</option>
                    <?php foreach ($regions as $r): ?>
                        <option value="<?= Security::e((string) $r['heures_ensoleillement']) ?>">
                            <?= Security::e($r['ville']) ?>
                            (<?= Security::e($fmt((float) $r['heures_ensoleillement'], 1)) ?> h/j)
                        </option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label" for="heures_ensoleillement">Heures d'ensoleillement / jour *</label>
                <input type="text" id="heures_ensoleillement" name="heures_ensoleillement"
                       class="form-control <?= isset($errors['heures_ensoleillement']) ? 'is-invalid' : '' ?>"
                       value="<?= Security::e($heuresVal) ?>" required>
                <?php if (isset($errors['heures_ensoleillement'])): ?>
                    <div class="invalid-feedback"><?= Security::e($errors['heures_ensoleillement']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-brand">
                <i class="fa-solid fa-calculator me-1"></i> Calculer et enregistrer
            </button>
            <a href="<?= Security::e(Url::to('photovoltaique')) ?>" class="btn btn-outline-secondary ms-1">Annuler</a>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="form-card calc-preview">
            <h2 class="h6 text-uppercase text-muted mb-3">Aperçu du dimensionnement</h2>
            <p class="small text-muted">Mis à jour automatiquement selon vos choix.</p>
            <div class="row g-2" id="pv-preview">
                <div class="col-6"><div class="preview-box"><span class="label">Panneaux</span><strong id="pv-nb">—</strong></div></div>
                <div class="col-6"><div class="preview-box"><span class="label">Puissance néces.</span><strong id="pv-kwc">—</strong></div></div>
                <div class="col-6"><div class="preview-box"><span class="label">Surface</span><strong id="pv-surf">—</strong></div></div>
                <div class="col-6"><div class="preview-box"><span class="label">Production/an</span><strong id="pv-prod">—</strong></div></div>
                <div class="col-6"><div class="preview-box"><span class="label">Couverture</span><strong id="pv-couv">—</strong></div></div>
                <div class="col-6"><div class="preview-box"><span class="label">Énergie STEG</span><strong id="pv-steg">—</strong></div></div>
                <div class="col-6"><div class="preview-box"><span class="label">Coût install.</span><strong id="pv-cout">—</strong></div></div>
                <div class="col-6"><div class="preview-box"><span class="label">ROI / an</span><strong id="pv-roi">—</strong></div></div>
                <div class="col-12"><div class="preview-box"><span class="label">Amortissement</span><strong id="pv-amort">—</strong></div></div>
            </div>
            <div id="pv-warning" class="alert alert-warning py-2 small mt-3 mb-0 d-none"></div>
        </div>
    </div>
</form>

<script>
window.GDC_PV_PANNEAUX = <?= json_encode($panneauxJson, JSON_UNESCAPED_UNICODE) ?>;
</script>
