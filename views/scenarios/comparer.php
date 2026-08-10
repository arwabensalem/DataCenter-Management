<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

$fmt = static fn(float $v, int $d = 0): string => number_format($v, $d, ',', ' ');
$meilleur = $decision['meilleur'] ?? null;
?>
<div class="mb-3">
    <a href="<?= Security::e(Url::to('scenarios')) ?>" class="text-muted text-decoration-none small">
        <i class="fa-solid fa-arrow-left me-1"></i> Scénarios
    </a>
    <h2 class="h4 mt-1"><?= Security::e($dataCenter['nom']) ?></h2>
</div>

<?php if ($meilleur): ?>
<div class="alert alert-success border-0 shadow-sm mb-4">
    <div class="d-flex gap-2 align-items-start">
        <i class="fa-solid fa-trophy mt-1"></i>
        <div>
            <strong>Scénario le plus performant : <?= Security::e($meilleur) ?></strong>
            <p class="mb-0 mt-1"><?= Security::e($decision['diagnostic']) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="table-card mb-4">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 scenario-compare">
            <thead>
                <tr>
                    <th>Indicateur</th>
                    <?php foreach ($scenarios as $key => $s): ?>
                        <th class="text-center <?= $key === $meilleur ? 'table-success' : '' ?>">
                            Scénario <?= Security::e($key) ?>
                            <?php if ($key === $meilleur): ?>
                                <span class="badge bg-success ms-1">Meilleur</span>
                            <?php endif; ?>
                            <div class="small fw-normal text-muted"><?= Security::e($s['libelle'] ?? '') ?></div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $rows = [
                    'consommation_annuelle' => ['Consommation annuelle (kWh)', 0],
                    'production_pv' => ['Production PV (kWh)', 0],
                    'couverture_solaire' => ['Couverture solaire (%)', 1],
                    'energie_steg' => ['Énergie STEG (kWh)', 0],
                    'cout_annuel' => ['Coût annuel (TND)', 0],
                    'roi' => ['ROI (%)', 1],
                    'temps_amortissement' => ['Amortissement (ans)', 1],
                    'co2_evite' => ['CO₂ évité (kg)', 0],
                    'score' => ['Score décisionnel', 1],
                ];
                foreach ($rows as $field => [$label, $dec]):
                ?>
                <tr>
                    <td><strong><?= Security::e($label) ?></strong></td>
                    <?php foreach ($scenarios as $key => $s): ?>
                        <td class="text-center <?= $key === $meilleur ? 'table-success' : '' ?>">
                            <?= Security::e($fmt((float) ($s[$field] ?? 0), $dec)) ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="form-card">
    <h3 class="h6 text-muted text-uppercase mb-2">Classement</h3>
    <ol class="mb-0">
        <?php foreach ($decision['classement'] as $i => $cle): ?>
            <li class="<?= $i === 0 ? 'fw-bold text-success' : '' ?>">
                Scénario <?= Security::e($cle) ?> —
                <?= Security::e($scenarios[$cle]['libelle'] ?? '') ?>
                (score <?= Security::e($fmt((float) $scenarios[$cle]['score'], 1)) ?>)
            </li>
        <?php endforeach; ?>
    </ol>
</div>
