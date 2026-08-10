<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Référentiel d'ensoleillement par ville.
 */
class RegionEnsoleillement extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->db->query(
            'SELECT * FROM regions_ensoleillement ORDER BY ville ASC'
        )->fetchAll();
    }

    /**
     * Recherche approximative par nom de ville.
     *
     * @return array<string, mixed>|null
     */
    public function findByVille(string $ville): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM regions_ensoleillement WHERE ville LIKE :ville LIMIT 1'
        );
        $stmt->execute(['ville' => '%' . $ville . '%']);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }
}
