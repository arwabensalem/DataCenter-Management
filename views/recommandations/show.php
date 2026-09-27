<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$prioriteBadge = static function (string $p): string {
    return match ($p) {
        'haute'   => 'bg-danger',
        'moyenne' => 'bg-warning text-dark',
        default   => 'bg-secondary',
    };
};

$prioriteIcon = static function (string $p): string {
    return match ($p) {
        'haute'   => 'fa-circle-exclamation',
        'moyenne' => 'fa-triangle-exclamation',
        default   => 'fa-circle-info',
    };
};
?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <a href="<?= Security::e(Url::to('recommandations')) ?>" class="text-decoration-none text-muted small">
            <i class="fa-solid fa-arrow-left me-1"></i> Recommandations
        </a>
        <h2 class="h4 mb-0 mt-1"><?= Security::e($dataCenter['nom']) ?></h2>
        <p class="text-muted mb-0"><?= Security::e($dataCenter['localisation']) ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
    <form method="post" action="<?= Security::e(Url::to('recommandations/generer/' . $dataCenter['id'])) ?>">
        <?= Security::csrfField() ?>
        <button type="submit" class="btn btn-brand">
            <i class="fa-solid fa-rotate me-1"></i> Régénérer
        </button>
    </form>
    <a href="<?= Security::e(Url::to('ai/analyze/' . (int) $dataCenter['id'])) ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-robot me-1"></i> Expliquer avec l'IA
    </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-teal" style="background:#dc3545"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div>
                <div class="stat-label">Priorité haute</div>
                <div class="stat-value"><?= (int) $counts['haute'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ffc107;color:#212529"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <div class="stat-label">Priorité moyenne</div>
                <div class="stat-value"><?= (int) $counts['moyenne'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fa-solid fa-list"></i></div>
            <div>
                <div class="stat-label">Total</div>
                <div class="stat-value"><?= count($recommandations) ?></div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($recommandations)): ?>
    <div class="alert alert-light border">
        Aucune recommandation enregistrée pour ce Data Center.
        Cliquez sur <strong>Régénérer</strong> pour lancer l'analyse.
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($recommandations as $r): ?>
            <div class="col-12">
                <div class="form-card reco-card reco-<?= Security::e($r['priorite']) ?>">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="reco-icon">
                            <i class="fa-solid <?= $prioriteIcon($r['priorite']) ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                <span class="badge <?= $prioriteBadge($r['priorite']) ?>">
                                    <?= Security::e(ucfirst($r['priorite'])) ?>
                                </span>
                                <span class="small text-muted">
                                    <?= Security::e($r['type']) ?>
                                    · <?= Security::e(date('d/m/Y H:i', strtotime($r['date_generation']))) ?>
                                </span>
                            </div>
                            <p class="mb-0"><?= Security::e($r['message']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-card mt-4">
    <h3 class="h6 text-uppercase text-muted mb-2">Règles métiers actives</h3>
    <ul class="small text-muted mb-0 ps-3">
        <li>Serveurs sous-utilisés (&lt; 40 %)</li>
        <li>Équipements énergivores (≥ 25 % de la conso)</li>
        <li>Part climatisation élevée (≥ 30 %)</li>
        <li>Surface PV insuffisante pour 100 % de couverture</li>
        <li>Couverture photovoltaïque insuffisante / faible</li>
        <li>Remplacement de serveurs gourmands recommandé</li>
        <li>Installation PV plus puissante / dépendance STEG</li>
    </ul>
</div>
