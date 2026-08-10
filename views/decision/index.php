<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;
?>
<div class="mb-4">
    <p class="text-muted mb-0">
        Diagnostic global : PUE, impact CO₂, alertes, scénarios et recommandations prioritaires.
    </p>
</div>
<div class="row g-3">
<?php foreach ($dataCenters as $dc): ?>
    <div class="col-md-6 col-xl-4">
        <div class="form-card h-100">
            <h3 class="h6 mb-1"><?= Security::e($dc['nom']) ?></h3>
            <p class="small text-muted"><?= Security::e($dc['gouvernorat_nom'] ?? $dc['localisation']) ?></p>
            <a href="<?= Security::e(Url::to('decision/' . $dc['id'])) ?>" class="btn btn-brand btn-sm">
                <i class="fa-solid fa-brain me-1"></i> Lancer le diagnostic
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>
