<?php

declare(strict_types=1);

namespace App\Models;

use PDOException;
use RuntimeException;

/**
 * Installation photovoltaïque (1 par Data Center).
 */
class InstallationPv extends Model
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByDataCenterId(int $dataCenterId): ?array
    {
        $sql = 'SELECT ipv.*,
                       tp.nom AS type_panneau_nom,
                       tp.puissance_wc,
                       tp.rendement,
                       tp.prix_unitaire,
                       tp.surface_m2,
                       dc.nom AS data_center_nom,
                       dc.entreprise_id,
                       dc.surface_disponible_pv,
                       dc.prix_kwh_steg,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id,
                       e.ville AS entreprise_ville
                FROM installations_pv ipv
                INNER JOIN types_panneaux tp ON tp.id = ipv.type_panneau_id
                INNER JOIN data_centers dc ON dc.id = ipv.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                WHERE ipv.data_center_id = :data_center_id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['data_center_id' => $dataCenterId]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT ipv.*,
                       tp.nom AS type_panneau_nom,
                       tp.puissance_wc,
                       tp.rendement,
                       tp.prix_unitaire,
                       tp.surface_m2,
                       dc.nom AS data_center_nom,
                       dc.entreprise_id,
                       dc.surface_disponible_pv,
                       dc.prix_kwh_steg,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id
                FROM installations_pv ipv
                INNER JOIN types_panneaux tp ON tp.id = ipv.type_panneau_id
                INNER JOIN data_centers dc ON dc.id = ipv.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id
                WHERE ipv.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Liste des installations avec contexte (filtrée éventuellement).
     *
     * @return list<array<string, mixed>>
     */
    public function allWithContext(?int $utilisateurId = null): array
    {
        $sql = 'SELECT ipv.*,
                       tp.nom AS type_panneau_nom,
                       dc.nom AS data_center_nom,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id
                FROM installations_pv ipv
                INNER JOIN types_panneaux tp ON tp.id = ipv.type_panneau_id
                INNER JOIN data_centers dc ON dc.id = ipv.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id';

        if ($utilisateurId !== null) {
            $sql .= ' WHERE e.utilisateur_id = :utilisateur_id';
        }

        $sql .= ' ORDER BY e.nom ASC, dc.nom ASC';

        if ($utilisateurId !== null) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['utilisateur_id' => $utilisateurId]);
            return $stmt->fetchAll();
        }

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Insert ou met à jour l'installation d'un Data Center.
     *
     * @param array<string, mixed> $data
     */
    public function upsert(int $dataCenterId, array $data): int
    {
        $existing = $this->findByDataCenterId($dataCenterId);

        try {
            if ($existing !== null) {
                $sql = 'UPDATE installations_pv SET
                            type_panneau_id = :type_panneau_id,
                            heures_ensoleillement = :heures_ensoleillement,
                            puissance_necessaire_kwc = :puissance_necessaire_kwc,
                            nombre_panneaux = :nombre_panneaux,
                            surface_necessaire = :surface_necessaire,
                            production_annuelle_kwh = :production_annuelle_kwh,
                            taux_couverture = :taux_couverture,
                            energie_pv_kwh = :energie_pv_kwh,
                            energie_steg_kwh = :energie_steg_kwh,
                            cout_installation = :cout_installation,
                            roi_pourcentage = :roi_pourcentage,
                            temps_amortissement = :temps_amortissement,
                            date_calcul = CURRENT_TIMESTAMP
                        WHERE data_center_id = :data_center_id';

                $params = $data;
                $params['data_center_id'] = $dataCenterId;
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);

                return (int) $existing['id'];
            }

            $sql = 'INSERT INTO installations_pv
                    (data_center_id, type_panneau_id, heures_ensoleillement,
                     puissance_necessaire_kwc, nombre_panneaux, surface_necessaire,
                     production_annuelle_kwh, taux_couverture, energie_pv_kwh,
                     energie_steg_kwh, cout_installation, roi_pourcentage, temps_amortissement)
                    VALUES
                    (:data_center_id, :type_panneau_id, :heures_ensoleillement,
                     :puissance_necessaire_kwc, :nombre_panneaux, :surface_necessaire,
                     :production_annuelle_kwh, :taux_couverture, :energie_pv_kwh,
                     :energie_steg_kwh, :cout_installation, :roi_pourcentage, :temps_amortissement)';

            $params = $data;
            $params['data_center_id'] = $dataCenterId;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException('Enregistrement PV impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    public function deleteByDataCenterId(int $dataCenterId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM installations_pv WHERE data_center_id = :id');
        return $stmt->execute(['id' => $dataCenterId]);
    }
}
