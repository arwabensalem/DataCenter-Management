<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0">Comparez des scénarios d'optimisation énergétique (AVANT / APRÈS).</p>
    <a href="<?= Security::e(Url::to('simulations/create')) ?>" class="btn btn-brand">
        <i class="fa-solid fa-plus me-1"></i> Nouvelle simulation
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Scénario</th>
                    <th>Data Center</th>
                    <?php if (Auth::isAdmin()): ?>
                        <th>Entreprise</th>
                    <?php endif; ?>
                    <th>Énergie écon.</th>
                    <th>Réduction</th>
                    <th>Coût écon.</th>
                    <th>CO₂ évité</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($simulations)): ?>
                <tr>
                    <td colspan="<?= Auth::isAdmin() ? 9 : 8 ?>" class="text-center text-muted py-5">
                        <i class="fa-solid fa-flask fa-2x mb-2 d-block opacity-50"></i>
                        Aucune simulation enregistrée.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($simulations as $s): ?>
                    <tr>
                        <td>
                            <strong><?= Security::e($s['nom']) ?></strong>
                            <?php if (!empty($s['description'])): ?>
                                <div class="small text-muted"><?= Security::e(mb_strimwidth($s['description'], 0, 60, '…')) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= Security::e($s['data_center_nom']) ?></td>
                        <?php if (Auth::isAdmin()): ?>
                            <td><?= Security::e($s['entreprise_nom']) ?></td>
                        <?php endif; ?>
                        <td class="<?= (float) $s['energie_economisee'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= Security::e($fmt((float) $s['energie_economisee'], 0)) ?> kWh
                        </td>
                        <td><?= Security::e($fmt((float) $s['pourcentage_reduction'], 1)) ?> %</td>
                        <td><?= Security::e($fmt((float) $s['cout_economise'], 0)) ?> TND</td>
                        <td><?= Security::e($fmt((float) $s['co2_evite'], 0)) ?> kg</td>
                        <td class="small"><?= Security::e(date('d/m/Y', strtotime($s['date_simulation']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= Security::e(Url::to('simulations/' . $s['id'])) ?>"
                               class="btn btn-sm btn-outline-secondary" title="Voir">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form method="post" action="<?= Security::e(Url::to('simulations/' . $s['id'] . '/delete')) ?>"
                                  class="d-inline" onsubmit="return confirm('Supprimer cette simulation ?');">
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
