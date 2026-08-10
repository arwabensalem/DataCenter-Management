<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Référentiel énergétique des gouvernorats tunisiens.
 */
class Gouvernorat extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->db->query(
            'SELECT * FROM gouvernorats ORDER BY nom ASC'
        )->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM gouvernorats WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByNom(string $nom): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM gouvernorats WHERE nom LIKE :nom LIMIT 1');
        $stmt->execute(['nom' => '%' . $nom . '%']);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Données pour la carte interactive (JSON).
     *
     * @return list<array<string, mixed>>
     */
    public function forMap(): array
    {
        $rows = $this->all();
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int) $r['id'],
                'code' => $r['code'],
                'nom' => $r['nom'],
                'lat' => (float) $r['latitude'],
                'lng' => (float) $r['longitude'],
                'irradiation' => (float) $r['irradiation_kwh_m2_an'],
                'heures' => (float) $r['heures_ensoleillement'],
                'potentiel' => $r['potentiel_pv'],
                'temperature' => $r['temperature_moyenne'] !== null
                    ? (float) $r['temperature_moyenne']
                    : null,
            ];
        }

        return $out;
    }
}
