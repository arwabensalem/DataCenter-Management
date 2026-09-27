# PUE — Power Usage Effectiveness

## 1. Introduction

Le PUE (Power Usage Effectiveness) est l'indicateur le plus utilisé pour évaluer l'efficacité énergétique globale d'un Data Center. Développé initialement par le consortium The Green Grid et repris par des organismes comme le DOE et l'ASHRAE, il a ensuite été formalisé dans la norme internationale ISO/IEC 30134-2. Ce document présente le PUE de façon pédagogique, sans imposer de seuils comme des normes universelles.

## 2. Définitions

- **PUE (Power Usage Effectiveness)** : rapport entre l'énergie totale consommée par l'installation (Total Facility Energy) et l'énergie consommée par les seuls équipements informatiques (IT Equipment Energy).
- **Total Facility Energy** : énergie totale entrant dans le Data Center, incluant l'IT, le refroidissement, l'UPS, la distribution électrique, l'éclairage et les autres charges auxiliaires.
- **IT Equipment Energy** : énergie consommée uniquement par les équipements de calcul, de stockage et de réseau.
- **DCiE (Data Center infrastructure Efficiency)** : indicateur complémentaire égal à l'inverse du PUE exprimé en pourcentage (DCiE = 1/PUE × 100).

## 3. Concepts principaux

Le PUE mesure la part d'énergie "additionnelle" nécessaire pour faire fonctionner l'infrastructure de support (refroidissement, alimentation, éclairage) par rapport à l'énergie directement utile aux équipements IT. Un PUE proche de 1 signifie qu'une part très importante de l'énergie totale est consacrée à l'IT et que les pertes d'infrastructure sont faibles. Un PUE plus élevé indique une part plus importante consacrée aux fonctions de support.

Le PUE est influencé principalement par :

- l'efficacité du système de refroidissement (poste le plus souvent déterminant) ;
- le rendement des UPS et de la distribution électrique ;
- le climat local (possibilité ou non de recourir au free cooling) ;
- le taux de charge du Data Center (un site sous-chargé a souvent un PUE moins favorable, l'infrastructure fonctionnant de façon peu efficiente à faible charge) ;
- l'âge et la conception de l'installation.

## 4. Méthodes / formules

**Formule générale :**

```
PUE = Total Facility Energy / IT Equipment Energy
```

**DCiE (complémentaire) :**

```
DCiE = (1 / PUE) × 100 %
```

L'ISO/IEC 30134-2 précise une méthodologie de mesure (catégories L1, L2, L3 selon le niveau de granularité et de fiabilité des mesures), afin de permettre des comparaisons plus cohérentes entre sites. Ce document ne reproduit pas le contenu intégral de la norme, qui est un document payant et protégé.

## 5. Bonnes pratiques

- Mesurer le PUE sur une période représentative (idéalement une année complète, pour couvrir les variations saisonnières), et non sur un instant isolé.
- Documenter le périmètre de mesure retenu (quels compteurs sont inclus dans "Total Facility Energy").
- Utiliser le PUE comme indicateur de suivi dans le temps pour un même site plutôt que comme seul critère de comparaison entre sites très différents (climat, taille, usage).
- Combiner le PUE avec d'autres indicateurs (WUE, CUE — voir document 11) pour une vision plus complète de la durabilité.

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (valeurs fictives, à but illustratif uniquement) :

Un Data Center consomme au total 1 200 kWh sur une journée, dont 800 kWh sont directement utilisés par les équipements IT.

```
PUE = 1200 / 800 = 1,5
```

Cela signifie que, dans cet exemple, pour chaque kWh consommé par l'IT, 0,5 kWh supplémentaire est consommé par l'infrastructure de support. Cette valeur est purement illustrative et ne doit pas être interprétée comme une référence sectorielle ou une cible à atteindre.

## 7. Limites et précautions

- Le PUE ne mesure pas l'efficacité des équipements IT eux-mêmes : un Data Center peut avoir un PUE faible tout en utilisant des serveurs très peu efficients.
- Le PUE seul ne renseigne pas sur la consommation absolue (un petit site très efficient peut avoir un PUE bas mais une consommation totale négligeable, et inversement).
- Les comparaisons de PUE entre sites de climats, tailles ou usages différents doivent être faites avec prudence.
- Aucun seuil de PUE ("bon" ou "mauvais") n'est présenté ici comme une norme universelle ; les seuils évoqués dans certaines communications commerciales ou médiatiques ne proviennent pas systématiquement de sources normatives.
- Le PUE ne doit pas être confondu avec la consommation totale d'énergie du site, qui reste l'indicateur pertinent pour évaluer l'impact énergétique et environnemental global.

## 8. Application à GreenDC Advisor

Ce document permet à l'assistant RAG de :

- expliquer le calcul et l'interprétation du PUE à un utilisateur souhaitant évaluer un Data Center ;
- distinguer clairement le PUE des indicateurs de consommation absolue, essentiels pour dimensionner une installation photovoltaïque ;
- mettre en relation les leviers d'amélioration du PUE avec les stratégies de refroidissement (document 03) ;
- rappeler la nécessité de préciser la période et le périmètre de mesure lorsqu'un PUE est communiqué par l'utilisateur.

## 9. Sources et références

- The Green Grid — concept originel du PUE (organisme à l'origine de la métrique, largement repris par l'industrie).
- U.S. Department of Energy (DOE) / FEMP — *Best Practices Guide for Energy-Efficient Data Center Design*.
- ASHRAE — Datacom Series, discussions sur l'efficacité énergétique et le PUE.
- ISO/IEC 30134-2:2016 — *Information technology — Data centres — Key performance indicators — Part 2: Power usage effectiveness (PUE)* (référence normative citée à titre indicatif ; contenu non reproduit).

Ce document est une synthèse pédagogique reformulée ; il ne remplace pas la consultation des textes normatifs originaux, notamment pour tout usage contractuel ou réglementaire.
