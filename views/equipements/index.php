<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <?php if ($filterDc): ?>
            <a href="<?= Security::e(Url::to('data-centers/' . $filterDc['id'])) ?>" class="text-decoration-none text-muted small">
                <i class="fa-solid fa-arrow-left me-1"></i> <?= Security::e($filterDc['nom']) ?>
            </a>
            <p class="text-muted mb-0 mt-1">Équipements du Data Center sélectionné.</p>
        <?php else: ?>
            <p class="text-muted mb-0">Inventaire énergétique — consommations calculées automatiquement.</p>
        <?php endif; ?>
    </div>
    <a href="<?= Security::e(Url::to('equipements/create' . ($filterDc ? '?data_center_id=' . $filterDc['id'] : ''))) ?>"
       class="btn btn-brand">
        <i class="fa-solid fa-plus me-1"></i> Nouvel équipement
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fa-solid fa-calendar-day"></i></div>
            <div>
                <div class="stat-label">Conso. journalière</div>
                <div class="stat-value"><?= Security::e($fmt((float) $totaux['journaliere'])) ?> <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-calendar"></i></div>
            <div>
                <div class="stat-label">Conso. mensuelle</div>
                <div class="stat-value"><?= Security::e($fmt((float) $totaux['mensuelle'])) ?> <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fa-solid fa-calendar-check"></i></div>
            <div>
                <div class="stat-label">Conso. annuelle</div>
                <div class="stat-value"><?= Security::e($fmt((float) $totaux['annuelle'], 0)) ?> <small class="fs-6">kWh</small></div>
            </div>
        </div>
    </div>
</div>

<?php if (count($dataCenters) > 1 || Auth::isAdmin()): ?>
<form method="get" action="<?= Security::e(Url::to('equipements')) ?>" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small text-muted mb-1" for="data_center_id">Filtrer par Data Center</label>
            <select name="data_center_id" id="data_center_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">— Tous —</option>
                <?php foreach ($dataCenters as $dc): ?>
                    <option value="<?= (int) $dc['id'] ?>"
                        <?= ($filterDc && (int) $filterDc['id'] === (int) $dc['id']) ? 'selected' : '' ?>>
                        <?= Security::e($dc['nom']) ?>
                        <?php if (Auth::isAdmin()): ?>
                            (<?= Security::e($dc['entreprise_nom']) ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>
<?php endif; ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Équipement</th>
                    <th>Catégorie</th>
                    <th>Data Center</th>
                    <th class="text-center">Qté</th>
                    <th>Puissance</th>
                    <th>Taux</th>
                    <th>kWh/j</th>
                    <th>kWh/an</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($equipements)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fa-solid fa-microchip fa-2x mb-2 d-block opacity-50"></i>
                        Aucun équipement enregistré.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($equipements as $eq): ?>
                    <tr>
                        <td>
                            <strong><?= Security::e($eq['nom']) ?></strong>
                            <div class="small text-muted">
                                <?= Security::e($eq['fabricant']) ?> · <?= Security::e($eq['modele']) ?>
                            </div>
                        </td>
                        <td><span class="badge bg-soft-green"><?= Security::e($eq['categorie_label']) ?></span></td>
                        <td><?= Security::e($eq['data_center_nom']) ?></td>
                        <td class="text-center"><?= (int) $eq['quantite'] ?></td>
                        <td><?= Security::e($fmt((float) $eq['puissance_watts'], 0)) ?> W</td>
                        <td><?= Security::e($fmt((float) $eq['taux_utilisation'], 0)) ?> %</td>
                        <td><strong><?= Security::e($fmt((float) $eq['conso_journaliere'])) ?></strong></td>
                        <td><?= Security::e($fmt((float) $eq['conso_annuelle'], 0)) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= Security::e(Url::to('equipements/' . $eq['id'])) ?>"
                               class="btn btn-sm btn-outline-secondary" title="Voir">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="<?= Security::e(Url::to('equipements/' . $eq['id'] . '/edit')) ?>"
                               class="btn btn-sm btn-outline-primary" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="post"
                                  action="<?= Security::e(Url::to('equipements/' . $eq['id'] . '/delete')) ?>"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer cet équipement ?');">
                                <?= Security::csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
