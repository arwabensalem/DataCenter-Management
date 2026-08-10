<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <p class="text-muted mb-0">Gérez les entreprises clientes et leurs comptes d'accès.</p>
    </div>
    <a href="<?= Security::e(Url::to('entreprises/create')) ?>" class="btn btn-brand">
        <i class="fa-solid fa-plus me-1"></i> Nouvelle entreprise
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Entreprise</th>
                    <th>Ville</th>
                    <th>Contact client</th>
                    <th>Téléphone</th>
                    <th class="text-center">Data Centers</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($entreprises)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-building fa-2x mb-2 d-block opacity-50"></i>
                        Aucune entreprise enregistrée.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($entreprises as $e): ?>
                    <tr>
                        <td>
                            <strong><?= Security::e($e['nom']) ?></strong>
                            <div class="small text-muted"><?= Security::e($e['email']) ?></div>
                        </td>
                        <td><?= Security::e($e['ville']) ?></td>
                        <td>
                            <?= Security::e($e['client_prenom'] . ' ' . $e['client_nom']) ?>
                            <div class="small text-muted"><?= Security::e($e['client_email']) ?></div>
                        </td>
                        <td><?= Security::e($e['telephone']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-soft-blue"><?= (int) $e['nb_data_centers'] ?></span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= Security::e(Url::to('entreprises/' . $e['id'])) ?>"
                               class="btn btn-sm btn-outline-secondary" title="Voir">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="<?= Security::e(Url::to('entreprises/' . $e['id'] . '/edit')) ?>"
                               class="btn btn-sm btn-outline-primary" title="Modifier">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="post"
                                  action="<?= Security::e(Url::to('entreprises/' . $e['id'] . '/delete')) ?>"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer cette entreprise, son compte client et toutes ses données associées ?');">
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
