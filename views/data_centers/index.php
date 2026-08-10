<?php

declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Url;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0">
        Caractéristiques techniques des infrastructures pour le dimensionnement énergétique.
    </p>
    <a href="<?= Security::e(Url::to('data-centers/create')) ?>" class="btn btn-brand">
        <i class="fa-solid fa-plus me-1"></i> Nouveau Data Center
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nom</th>
                    <?php if (Auth::isAdmin()): ?>
                        <th>Entreprise</th>
                    <?php endif; ?>
                    <th>Localisation</th>
                    <th>Surface totale</th>
                    <th>Surface PV</th>
                    <th>Prix kWh</th>
                    <th>h/jour</th>
                    <th class="text-center">Équip.</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($dataCenters)): ?>
                <tr>
                    <td colspan="<?= Auth::isAdmin() ? 9 : 8 ?>" class="text-center text-muted py-5">
                        <i class="fa-solid fa-server fa-2x mb-2 d-block opacity-50"></i>
                        Aucun Data Center enregistré.
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
                        <td><?= Security::e(number_format((float) $dc['surface_totale'], 2, ',', ' ')) ?> m²</td>
                        <td><?= Security::e(number_format((float) $dc['surface_disponible_pv'], 2, ',', ' ')) ?> m²</td>
                        <td><?= Security::e(number_format((float) $dc['prix_kwh_steg'], 4, ',', ' ')) ?> TND</td>
                        <td><?= Security::e(number_format((float) $dc['heures_fonctionnement'], 1, ',', ' ')) ?></td>
                        <td class="text-center">
                            <span class="badge bg-soft-blue"><?= (int) $dc['nb_equipements'] ?></span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= Security::e(Url::to('data-centers/' . $dc['id'])) ?>"
                               class="btn btn-sm btn-outline-secondary" title="Voir">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="<?= Security::e(Url::to('data-centers/' . $dc['id'] . '/edit')) ?>"
                               class="btn btn-sm btn-outline-primary" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="post"
                                  action="<?= Security::e(Url::to('data-centers/' . $dc['id'] . '/delete')) ?>"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer ce Data Center et ses équipements associés ?');">
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
