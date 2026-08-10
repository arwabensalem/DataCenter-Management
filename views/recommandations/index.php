<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$prioriteBadge = static function (string $p): string {
    return match ($p) {
        'haute'   => 'bg-danger',
        'moyenne' => 'bg-warning text-dark',
        default   => 'bg-secondary',
    };
};
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0">
        Recommandations automatiques basées sur des règles métiers (sans IA externe).
    </p>
    <form method="post" action="<?= Security::e(Url::to('recommandations/generer-tout')) ?>">
        <?= Security::csrfField() ?>
        <button type="submit" class="btn btn-brand">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Analyser tous les Data Centers
        </button>
    </form>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($dataCenters as $dc): ?>
        <div class="col-md-6 col-xl-4">
            <div class="form-card h-100 d-flex flex-column">
                <h3 class="h6 mb-1"><?= Security::e($dc['nom']) ?></h3>
                <?php if (Auth::isAdmin()): ?>
                    <p class="small text-muted mb-3"><?= Security::e($dc['entreprise_nom']) ?></p>
                <?php else: ?>
                    <p class="small text-muted mb-3"><?= Security::e($dc['localisation']) ?></p>
                <?php endif; ?>
                <div class="mt-auto d-flex gap-2">
                    <a href="<?= Security::e(Url::to('recommandations/' . $dc['id'])) ?>"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-eye me-1"></i> Voir
                    </a>
                    <form method="post" action="<?= Security::e(Url::to('recommandations/generer/' . $dc['id'])) ?>">
                        <?= Security::csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-brand">
                            <i class="fa-solid fa-lightbulb me-1"></i> Générer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($dataCenters)): ?>
        <div class="col-12">
            <div class="alert alert-light border">Aucun Data Center disponible.</div>
        </div>
    <?php endif; ?>
</div>

<div class="table-card">
    <h2 class="h6 text-uppercase text-muted mb-3 px-1">Dernières recommandations</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Priorité</th>
                    <th>Data Center</th>
                    <?php if (Auth::isAdmin()): ?><th>Entreprise</th><?php endif; ?>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($recommandations)): ?>
                <tr>
                    <td colspan="<?= Auth::isAdmin() ? 5 : 4 ?>" class="text-center text-muted py-4">
                        Aucune recommandation. Cliquez sur « Analyser » pour lancer le moteur de règles.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($recommandations as $r): ?>
                    <tr>
                        <td>
                            <span class="badge <?= $prioriteBadge($r['priorite']) ?>">
                                <?= Security::e(ucfirst($r['priorite'])) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= Security::e(Url::to('recommandations/' . $r['data_center_id'])) ?>">
                                <?= Security::e($r['data_center_nom']) ?>
                            </a>
                        </td>
                        <?php if (Auth::isAdmin()): ?>
                            <td><?= Security::e($r['entreprise_nom']) ?></td>
                        <?php endif; ?>
                        <td class="small"><?= Security::e($r['message']) ?></td>
                        <td class="small text-nowrap"><?= Security::e(date('d/m/Y H:i', strtotime($r['date_generation']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
