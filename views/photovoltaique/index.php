<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0">
        Dimensionnez une installation solaire adaptée à la consommation de chaque Data Center.
    </p>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Data Center</th>
                    <?php if (Auth::isAdmin()): ?>
                        <th>Entreprise</th>
                    <?php endif; ?>
                    <th>Surface PV</th>
                    <th>Statut</th>
                    <th>Couverture</th>
                    <th>ROI</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($dataCenters)): ?>
                <tr>
                    <td colspan="<?= Auth::isAdmin() ? 7 : 6 ?>" class="text-center text-muted py-5">
                        Aucun Data Center disponible.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($dataCenters as $dc): ?>
                    <?php $inst = $installations[(int) $dc['id']] ?? null; ?>
                    <tr>
                        <td><strong><?= Security::e($dc['nom']) ?></strong></td>
                        <?php if (Auth::isAdmin()): ?>
                            <td><?= Security::e($dc['entreprise_nom']) ?></td>
                        <?php endif; ?>
                        <td><?= Security::e($fmt((float) $dc['surface_disponible_pv'], 0)) ?> m²</td>
                        <td>
                            <?php if ($inst): ?>
                                <span class="badge bg-soft-green">Dimensionné</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">À dimensionner</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $inst ? Security::e($fmt((float) $inst['taux_couverture'], 1)) . ' %' : '—' ?>
                        </td>
                        <td>
                            <?= $inst ? Security::e($fmt((float) $inst['roi_pourcentage'], 1)) . ' %' : '—' ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php if ($inst): ?>
                                <a href="<?= Security::e(Url::to('photovoltaique/resultats/' . $dc['id'])) ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Résultats">
                                    <i class="fa-solid fa-chart-pie"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?= Security::e(Url::to('photovoltaique/dimensionner/' . $dc['id'])) ?>"
                               class="btn btn-sm btn-brand" title="Dimensionner">
                                <i class="fa-solid fa-solar-panel"></i>
                                <?= $inst ? 'Recalculer' : 'Dimensionner' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
