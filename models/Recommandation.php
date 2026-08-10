<?php

declare(strict_types=1);

namespace App\Models;

use PDOException;
use RuntimeException;

/**
 * Recommandations générées par le moteur de règles.
 */
class Recommandation extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findByDataCenterId(int $dataCenterId): array
    {
        $sql = 'SELECT * FROM recommandations
                WHERE data_center_id = :data_center_id
                ORDER BY FIELD(priorite, \'haute\', \'moyenne\', \'basse\'), date_generation DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['data_center_id' => $dataCenterId]);

        return $stmt->fetchAll();
    }

    /**
     * Dernières recommandations par DC (vue globale).
     *
     * @return list<array<string, mixed>>
     */
    public function latestByAccessibleDcs(?int $utilisateurId = null): array
    {
        $sql = 'SELECT r.*,
                       dc.nom AS data_center_nom,
                       e.nom AS entreprise_nom,
                       e.utilisateur_id AS entreprise_utilisateur_id
                FROM recommandations r
                INNER JOIN data_centers dc ON dc.id = r.data_center_id
                INNER JOIN entreprises e ON e.id = dc.entreprise_id';

        if ($utilisateurId !== null) {
            $sql .= ' WHERE e.utilisateur_id = :utilisateur_id';
        }

        $sql .= ' ORDER BY r.date_generation DESC, FIELD(r.priorite, \'haute\', \'moyenne\', \'basse\')
                  LIMIT 100';

        if ($utilisateurId !== null) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['utilisateur_id' => $utilisateurId]);
            return $stmt->fetchAll();
        }

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Remplace toutes les recommandations d'un DC par un nouveau jeu.
     *
     * @param list<array{type: string, priorite: string, message: string}> $items
     */
    public function replaceForDataCenter(int $dataCenterId, array $items): int
    {
        try {
            $this->db->beginTransaction();

            $del = $this->db->prepare('DELETE FROM recommandations WHERE data_center_id = :id');
            $del->execute(['id' => $dataCenterId]);

            $ins = $this->db->prepare(
                'INSERT INTO recommandations (data_center_id, type, priorite, message)
                 VALUES (:data_center_id, :type, :priorite, :message)'
            );

            foreach ($items as $item) {
                $ins->execute([
                    'data_center_id' => $dataCenterId,
                    'type'           => $item['type'],
                    'priorite'       => $item['priorite'],
                    'message'        => $item['message'],
                ]);
            }

            $this->db->commit();
            return count($items);
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new RuntimeException('Enregistrement des recommandations impossible : ' . $e->getMessage(), 0, $e);
        }
    }

    public function countByPriorite(int $dataCenterId): array
    {
        $sql = 'SELECT priorite, COUNT(*) AS total
                FROM recommandations
                WHERE data_center_id = :id
                GROUP BY priorite';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $dataCenterId]);

        $result = ['haute' => 0, 'moyenne' => 0, 'basse' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['priorite']] = (int) $row['total'];
        }

        return $result;
    }
}
