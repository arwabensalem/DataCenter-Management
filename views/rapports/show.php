<?php

declare(strict_types=1);

use App\Helpers\Security;

$fmt = static fn(float $v, int $dec = 2): string => number_format($v, $dec, ',', ' ');
?>
<header class="report-header mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div class="report-brand">
                <i class="fa-solid fa-solar-panel me-2"></i>GreenDC Advisor
            </div>
            <h1 class="h3 mb-1">Rapport d'aide à la décision énergétique</h1>
            <p class="text-muted mb-0">
                Data Center : <strong><?= Security::e($dataCenter['nom']) ?></strong>
            </p>
        </div>
        <div class="text-end small text-muted">
            <div>Généré le <?= Security::e($generatedAt) ?></div>
            <div>Par <?= Security::e($auteur) ?></div>
        </div>
    </div>
</header>

<section class="report-section mb-4">
    <h2 class="report-title">1. Informations de l'entreprise</h2>
    <?php if ($entreprise): ?>
        <div class="row">
            <div class="col-md-6">
                <dl class="row detail-list mb-0">
                    <dt class="col-5">Raison sociale</dt>
                    <dd class="col-7"><?= Security::e($entreprise['nom']) ?></dd>
                    <dt class="col-5">Adresse</dt>
                    <dd class="col-7"><?= Security::e($entreprise['adresse']) ?></dd>
                    <dt class="col-5">Ville</dt>
                    <dd class="col-7"><?= Security::e($entreprise['ville']) ?></dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row detail-list mb-0">
                    <dt class="col-5">Téléphone</dt>
                    <dd class="col-7"><?= Security::e($entreprise['telephone']) ?></dd>
                    <dt class="col-5">E-mail</dt>
                    <dd class="col-7"><?= Security::e($entreprise['email']) ?></dd>
                    <dt class="col-5">Contact client</dt>
                    <dd class="col-7">
                        <?= Security::e(($entreprise['client_prenom'] ?? '') . ' ' . ($entreprise['client_nom'] ?? '')) ?>
                    </dd>
                </dl>
            </div>
        </div>
    <?php else: ?>
        <p class="text-muted mb-0">Entreprise non trouvée.</p>
    <?php endif; ?>
</section>

<section class="report-section mb-4">
    <h2 class="report-title">2. Informations du Data Center</h2>
    <div class="row">
        <div class="col-md-6">
            <dl class="row detail-list mb-0">
                <dt class="col-6">Nom</dt>
                <dd class="col-6"><?= Security::e($dataCenter['nom']) ?></dd>
                <dt class="col-6">Localisation</dt>
                <dd class="col-6"><?= Security::e($dataCenter['localisation']) ?></dd>
                <dt class="col-6">Surface totale</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $dataCenter['surface_totale'], 0)) ?> m²</dd>
            </dl>
        </div>
        <div class="col-md-6">
            <dl class="row detail-list mb-0">
                <dt class="col-6">Surface PV disponible</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $dataCenter['surface_disponible_pv'], 0)) ?> m²</dd>
                <dt class="col-6">Prix kWh STEG</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $dataCenter['prix_kwh_steg'], 4)) ?> TND</dd>
                <dt class="col-6">Heures / jour</dt>
                <dd class="col-6"><?= Security::e($fmt((float) $dataCenter['heures_fonctionnement'], 1)) ?> h</dd>
            </dl>
        </div>
    </div>
</section>

<section class="report-section mb-4">
    <h2 class="report-title">3. Consommation énergétique</h2>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="kpi-box">
                <div class="label">Journalière</div>
                <div class="value"><?= Security::e($fmt((float) $totaux['journaliere'])) ?> kWh</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-box">
                <div class="label">Mensuelle</div>
                <div class="value"><?= Security::e($fmt((float) $totaux['mensuelle'])) ?> kWh</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-box">
                <div class="label">Annuelle</div>
                <div class="value"><?= Security::e($fmt((float) $totaux['annuelle'], 0)) ?> kWh</div>
            </div>
        </div>
    </div>

    <table class="table table-sm table-bordered report-table">
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Catégorie</th>
                <th class="text-center">Qté</th>
                <th>Puissance</th>
                <th>Taux</th>
                <th>kWh/j</th>
                <th>kWh/an</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($equipements)): ?>
            <tr><td colspan="7" class="text-muted">Aucun équipement.</td></tr>
        <?php else: ?>
            <?php foreach ($equipements as $eq): ?>
                <tr>
                    <td><?= Security::e($eq['nom']) ?></td>
                    <td><?= Security::e($eq['categorie_label']) ?></td>
                    <td class="text-center"><?= (int) $eq['quantite'] ?></td>
                    <td><?= Security::e($fmt((float) $eq['puissance_watts'], 0)) ?> W</td>
                    <td><?= Security::e($fmt((float) $eq['taux_utilisation'], 0)) ?> %</td>
                    <td><?= Security::e($fmt((float) $eq['conso_journaliere'])) ?></td>
                    <td><?= Security::e($fmt((float) $eq['conso_annuelle'], 0)) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <p class="small text-muted mb-0">
        Coût annuel STEG estimé : <strong><?= Security::e($fmt($coutSteg, 0)) ?> TND</strong>
    </p>
</section>

<section class="report-section mb-4">
    <h2 class="report-title">4. Dimensionnement photovoltaïque</h2>
    <?php if ($installation === null): ?>
        <p class="text-muted mb-0">Aucun dimensionnement photovoltaïque enregistré pour ce Data Center.</p>
    <?php else: ?>
        <div class="row">
            <div class="col-md-6">
                <dl class="row detail-list mb-0">
                    <dt class="col-7">Type de panneau</dt>
                    <dd class="col-5"><?= Security::e($installation['type_panneau_nom']) ?></dd>
                    <dt class="col-7">Nombre de panneaux</dt>
                    <dd class="col-5"><?= (int) $installation['nombre_panneaux'] ?></dd>
                    <dt class="col-7">Puissance nécessaire</dt>
                    <dd class="col-5"><?= Security::e($fmt((float) $installation['puissance_necessaire_kwc'], 2)) ?> kWc</dd>
                    <dt class="col-7">Surface nécessaire</dt>
                    <dd class="col-5"><?= Security::e($fmt((float) $installation['surface_necessaire'], 1)) ?> m²</dd>
                    <dt class="col-7">Ensoleillement</dt>
                    <dd class="col-5"><?= Security::e($fmt((float) $installation['heures_ensoleillement'], 1)) ?> h/j</dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row detail-list mb-0">
                    <dt class="col-7">Production annuelle</dt>
                    <dd class="col-5"><?= Security::e($fmt((float) $installation['production_annuelle_kwh'], 0)) ?> kWh</dd>
                    <dt class="col-7">Couverture</dt>
                    <dd class="col-5"><?= Security::e($fmt((float) $installation['taux_couverture'], 1)) ?> %</dd>
                    <dt class="col-7">Énergie PV / STEG</dt>
                    <dd class="col-5">
                        <?= Security::e($fmt((float) $installation['energie_pv_kwh'], 0)) ?>
                        /
                        <?= Security::e($fmt((float) $installation['energie_steg_kwh'], 0)) ?> kWh
                    </dd>
                    <dt class="col-7">Coût installation</dt>
                    <dd class="col-5"><?= Security::e($fmt((float) $installation['cout_installation'], 0)) ?> TND</dd>
                    <dt class="col-7">ROI / Amortissement</dt>
                    <dd class="col-5">
                        <?= Security::e($fmt((float) $installation['roi_pourcentage'], 1)) ?> %
                        /
                        <?= Security::e($fmt((float) $installation['temps_amortissement'], 1)) ?> ans
                    </dd>
                    <dt class="col-7">Économie / CO₂ évité</dt>
                    <dd class="col-5">
                        <?= Security::e($fmt($economie, 0)) ?> TND
                        /
                        <?= Security::e($fmt($co2Evite, 0)) ?> kg
                    </dd>
                </dl>
            </div>
        </div>
    <?php endif; ?>
</section>

<section class="report-section mb-4">
    <h2 class="report-title">5. Résultats des simulations</h2>
    <?php if (empty($simulations)): ?>
        <p class="text-muted mb-0">Aucune simulation enregistrée.</p>
    <?php else: ?>
        <table class="table table-sm table-bordered report-table">
            <thead>
                <tr>
                    <th>Scénario</th>
                    <th>Énergie écon.</th>
                    <th>Réduction</th>
                    <th>Coût écon.</th>
                    <th>↓ STEG</th>
                    <th>CO₂ évité</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($simulations as $s): ?>
                    <tr>
                        <td>
                            <strong><?= Security::e($s['nom']) ?></strong>
                            <?php if (!empty($s['description'])): ?>
                                <div class="small text-muted"><?= Security::e($s['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= Security::e($fmt((float) $s['energie_economisee'], 0)) ?> kWh</td>
                        <td><?= Security::e($fmt((float) $s['pourcentage_reduction'], 1)) ?> %</td>
                        <td><?= Security::e($fmt((float) $s['cout_economise'], 0)) ?> TND</td>
                        <td><?= Security::e($fmt((float) $s['reduction_dependance_steg'], 1)) ?> pts</td>
                        <td><?= Security::e($fmt((float) $s['co2_evite'], 0)) ?> kg</td>
                        <td><?= Security::e(date('d/m/Y', strtotime($s['date_simulation']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="report-section mb-4">
    <h2 class="report-title">6. Recommandations</h2>
    <?php if (empty($recommandations)): ?>
        <p class="text-muted mb-0">
            Aucune recommandation. Lancez le moteur de règles depuis le module Recommandations.
        </p>
    <?php else: ?>
        <ol class="report-reco-list mb-0">
            <?php foreach ($recommandations as $r): ?>
                <li class="mb-2">
                    <span class="badge reco-badge-<?= Security::e($r['priorite']) ?>">
                        <?= Security::e(ucfirst($r['priorite'])) ?>
                    </span>
                    <?= Security::e($r['message']) ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

<section class="report-section mb-4 avoid-break">
    <h2 class="report-title">7. Graphiques</h2>
    <div class="row g-4">
        <div class="col-md-8">
            <h3 class="h6 text-muted">Consommation &amp; production mensuelles</h3>
            <canvas id="chartMensuel" height="110"></canvas>
        </div>
        <div class="col-md-4">
            <h3 class="h6 text-muted">Mix énergétique</h3>
            <canvas id="chartMix" height="180"></canvas>
        </div>
        <div class="col-md-6">
            <h3 class="h6 text-muted">Répartition par équipement</h3>
            <canvas id="chartRepartition" height="150"></canvas>
        </div>
        <div class="col-md-6">
            <h3 class="h6 text-muted">Production vs Consommation</h3>
            <canvas id="chartComparaison" height="150"></canvas>
        </div>
    </div>
</section>

<section class="report-section mb-4">
    <h2 class="report-title">8. Conclusion &amp; aide à la décision</h2>
    <?php if (!empty($decision['diagnostic'])): ?>
        <div class="p-3 border rounded bg-light">
            <p class="mb-2"><strong>Scénario recommandé :
                <?= Security::e((string) ($decision['meilleur'] ?? '—')) ?></strong></p>
            <p class="mb-0"><?= Security::e($decision['diagnostic']) ?></p>
        </div>
    <?php else: ?>
        <p class="text-muted mb-0">Diagnostic non disponible.</p>
    <?php endif; ?>
</section>

<footer class="report-footer mt-5 pt-3">
    <p class="small text-muted mb-0">
        Document généré par <strong>GreenDC Advisor</strong> — plateforme d'aide à la décision
        pour le dimensionnement photovoltaïque et l'optimisation énergétique des Data Centers.
        Les consommations et indicateurs sont calculés automatiquement ; ce rapport ne constitue
        pas un devis commercial.
    </p>
</footer>

<script>
window.GDC_CHARTS = <?= json_encode($charts, JSON_UNESCAPED_UNICODE) ?>;
</script>
