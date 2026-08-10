<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Catalogue des types de panneaux photovoltaïques.
 */
class TypePanneau extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function allActifs(): array
    {
        $sql = 'SELECT * FROM types_panneaux WHERE statut = \'actif\' ORDER BY puissance_wc ASC';
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM types_panneaux WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }
}
