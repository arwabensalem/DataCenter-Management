<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0">
        Générez un rapport décisionnel imprimable (PDF via l'imprimante du navigateur).
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
                    <th>Localisation</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($dataCenters)): ?>
                <tr>
                    <td colspan="<?= Auth::isAdmin() ? 4 : 3 ?>" class="text-center text-muted py-5">
                        Aucun Data Center disponible.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($dataCenters as $dc): ?>
                    <tr>
                        <td><strong><?= Security::e($dc['nom']) ?></strong></td>
                        <?php if (Auth::isAdmin()): ?>
                            <td><?= Security::e($dc['entreprise_nom']) ?></td>
                        <?php endif; ?>
                        <td><?= Security::e($dc['localisation']) ?></td>
                        <td class="text-end">
                            <a href="<?= Security::e(Url::to('rapports/' . $dc['id'])) ?>"
                               class="btn btn-brand btn-sm" target="_blank">
                                <i class="fa-solid fa-file-lines me-1"></i> Générer le rapport
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
