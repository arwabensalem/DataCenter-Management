<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Calculs énergétiques réutilisables (consommations équipements).
 * Les résultats ne sont jamais saisis par l'utilisateur.
 */
final class EnergyCalculator
{
    /**
     * Catégories d'équipements autorisées.
     *
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'serveur'        => 'Serveur',
            'switch'         => 'Switch',
            'routeur'        => 'Routeur',
            'firewall'       => 'Firewall',
            'stockage'       => 'Stockage',
            'ups'            => 'UPS',
            'climatisation'  => 'Climatisation',
        ];
    }

    /**
     * Libellé d'une catégorie.
     */
    public static function categoryLabel(string $code): string
    {
        return self::categories()[$code] ?? $code;
    }

    /**
     * Consommation quotidienne d'un équipement (kWh).
     *
     * Formule :
     * (puissance_W × taux%/100 × quantité × heures) / 1000
     */
    public static function consoJournaliere(
        float $puissanceWatts,
        float $tauxUtilisation,
        int $quantite,
        float $heuresFonctionnement
    ): float {
        $effective = $puissanceWatts * ($tauxUtilisation / 100);

        return round(($effective * $quantite * $heuresFonctionnement) / 1000, 4);
    }

    /**
     * Consommation mensuelle (kWh) — 30 jours.
     */
    public static function consoMensuelle(float $consoJournaliere): float
    {
        return round($consoJournaliere * 30, 4);
    }

    /**
     * Consommation annuelle (kWh) — 365 jours.
     */
    public static function consoAnnuelle(float $consoJournaliere): float
    {
        return round($consoJournaliere * 365, 4);
    }

    /**
     * Enrichit un équipement avec ses consommations calculées.
     *
     * @param array<string, mixed> $equipement
     * @return array<string, mixed>
     */
    public static function enrich(array $equipement): array
    {
        $jour = self::consoJournaliere(
            (float) $equipement['puissance_watts'],
            (float) $equipement['taux_utilisation'],
            (int) $equipement['quantite'],
            (float) $equipement['heures_fonctionnement']
        );

        $equipement['conso_journaliere'] = $jour;
        $equipement['conso_mensuelle'] = self::consoMensuelle($jour);
        $equipement['conso_annuelle'] = self::consoAnnuelle($jour);
        $equipement['categorie_label'] = self::categoryLabel((string) $equipement['categorie']);

        return $equipement;
    }

    /**
     * Totaux de consommation pour une liste d'équipements.
     *
     * @param list<array<string, mixed>> $equipements
     * @return array{journaliere: float, mensuelle: float, annuelle: float, par_categorie: array<string, float>}
     */
    public static function totaux(array $equipements): array
    {
        $jour = 0.0;
        $parCategorie = [];

        foreach ($equipements as $eq) {
            $enriched = isset($eq['conso_journaliere']) ? $eq : self::enrich($eq);
            $jour += (float) $enriched['conso_journaliere'];

            $cat = (string) $enriched['categorie'];
            $parCategorie[$cat] = ($parCategorie[$cat] ?? 0.0) + (float) $enriched['conso_annuelle'];
        }

        return [
            'journaliere'   => round($jour, 4),
            'mensuelle'     => self::consoMensuelle($jour),
            'annuelle'      => self::consoAnnuelle($jour),
            'par_categorie' => $parCategorie,
        ];
    }

    /**
     * Coût annuel estimé (TND) à partir de la conso annuelle et du prix kWh.
     */
    public static function coutAnnuel(float $consoAnnuelleKwh, float $prixKwh): float
    {
        return round($consoAnnuelleKwh * $prixKwh, 2);
    }
}
