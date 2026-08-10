<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;
?>
<div class="mb-4">
    <p class="text-muted mb-0">Exportez les résultats au format Excel ou générez le rapport PDF imprimable.</p>
</div>
<div class="table-card">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Data Center</th>
                <th class="text-end">Exports</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($dataCenters as $dc): ?>
            <tr>
                <td><strong><?= Security::e($dc['nom']) ?></strong></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-success" href="<?= Security::e(Url::to('exports/excel/' . $dc['id'])) ?>">
                        <i class="fa-solid fa-file-excel me-1"></i> Excel
                    </a>
                    <a class="btn btn-sm btn-brand" href="<?= Security::e(Url::to('rapports/' . $dc['id'])) ?>" target="_blank">
                        <i class="fa-solid fa-file-pdf me-1"></i> PDF / Imprimer
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
