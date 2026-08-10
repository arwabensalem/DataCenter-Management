<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;
?>
<div class="mb-4">
    <p class="text-muted mb-0">Comparez les scénarios A / B / C et identifiez automatiquement le plus performant.</p>
</div>
<div class="table-card">
    <table class="table table-hover mb-0">
        <thead>
            <tr><th>Data Center</th><th>Gouvernorat</th><th class="text-end">Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($dataCenters as $dc): ?>
            <tr>
                <td><strong><?= Security::e($dc['nom']) ?></strong></td>
                <td><?= Security::e($dc['gouvernorat_nom'] ?? '—') ?></td>
                <td class="text-end">
                    <a class="btn btn-brand btn-sm" href="<?= Security::e(Url::to('scenarios/' . $dc['id'])) ?>">
                        Comparer
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($dataCenters)): ?>
            <tr><td colspan="3" class="text-center text-muted py-4">Aucun Data Center.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
