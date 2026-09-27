# Data Center Energy Efficiency

## 1. Introduction

L'efficacité énergétique d'un Data Center désigne la capacité d'une infrastructure informatique à fournir un service de calcul, de stockage et de réseau donné en consommant le minimum d'énergie possible, tout en maintenant les niveaux de disponibilité et de fiabilité requis. Ce document synthétise les concepts de base et les bonnes pratiques reconnues internationalement (notamment par le Department of Energy américain — DOE — et l'ASHRAE) pour l'analyse et l'amélioration de la performance énergétique des Data Centers.

Ce document est une **synthèse pédagogique** reformulée à partir de sources publiques reconnues. Il ne remplace pas la lecture des documents originaux et ne constitue pas une norme.

## 2. Définitions

- **Data Center** : installation regroupant des équipements informatiques (serveurs, stockage, réseau) et les infrastructures nécessaires à leur fonctionnement (alimentation électrique, refroidissement, sécurité).
- **Efficacité énergétique** : rapport entre l'énergie utile délivrée (calcul, stockage, transmission de données) et l'énergie totale consommée pour délivrer ce service.
- **Charge IT (IT Load)** : puissance électrique consommée par les équipements informatiques eux-mêmes (hors infrastructure de support).
- **Charge d'infrastructure (Facility Load)** : puissance consommée par les systèmes de support : refroidissement, alimentation sans interruption (UPS), distribution électrique, éclairage.
- **Benchmarking énergétique** : comparaison des performances énergétiques d'une installation par rapport à des installations similaires ou à ses propres performances historiques.

## 3. Concepts principaux

### 3.1 Principales sources de consommation

Un Data Center consomme de l'énergie à travers plusieurs postes principaux :

- **Équipements IT** : serveurs, baies de stockage, équipements réseau (switches, routeurs, firewalls) ; c'est la charge utile du Data Center.
- **Serveurs** : consommation liée au processeur (CPU), à la mémoire, aux disques et aux alimentations internes ; fortement dépendante du taux d'utilisation.
- **Stockage** : baies de disques (HDD/SSD), systèmes de sauvegarde ; consommation liée à la capacité installée et à l'activité d'accès aux données.
- **Réseau** : équipements d'interconnexion (switches, routeurs) dont la consommation croît avec le débit et le nombre de ports actifs.
- **Alimentation sans interruption (UPS)** : convertit et conditionne l'énergie électrique ; génère des pertes de conversion (chaleur) qui varient selon la technologie (UPS statique double conversion, line-interactive, etc.) et le taux de charge.
- **Refroidissement** : système souvent le second poste de consommation après l'IT ; comprend les unités de climatisation (CRAC/CRAH), les groupes froids (chillers), les tours de refroidissement et les ventilateurs.
- **Éclairage** : poste généralement mineur mais optimisable (éclairage à la demande, LED, détecteurs de présence).
- **Pertes électriques** : pertes dans les transformateurs, câblages, onduleurs et distribution électrique (PDU) ; elles augmentent avec la longueur des circuits et le nombre de conversions AC/DC.

### 3.2 Stratégies générales d'amélioration

- Optimiser le taux d'utilisation des serveurs (éviter les serveurs sous-utilisés ou "zombies").
- Adapter la température et l'humidité de la salle informatique aux plages recommandées par l'ASHRAE plutôt qu'à des consignes historiquement trop conservatrices.
- Mettre en œuvre une gestion des flux d'air (containment allée chaude/allée froide).
- Recourir au free cooling lorsque le climat le permet.
- Consolider et virtualiser les charges de travail.
- Remplacer les équipements anciens par du matériel plus efficient (alimentations à haut rendement, disques SSD).
- Réduire le nombre d'étages de conversion électrique (AC/DC, DC/AC).

### 3.3 Monitoring énergétique et benchmarking

Le suivi énergétique repose sur :

- des compteurs et capteurs installés au niveau de l'alimentation générale, des rangées de baies (PDU intelligents), et parfois au niveau du serveur ;
- des indicateurs de performance énergétique (voir document `11_data_center_sustainability_metrics.md` pour le détail du PUE, WUE, CUE) ;
- une comparaison dans le temps (avant/après optimisation) et, lorsque c'est pertinent, avec des installations comparables (benchmarking).

Le DOE recommande une démarche de mesure continue plutôt qu'une évaluation ponctuelle, afin de détecter les dérives de performance dans la durée.

## 4. Méthodes / formules

L'efficacité énergétique globale peut être approchée par le rapport :

```
Efficacité = Énergie utile (calcul/stockage/réseau) / Énergie totale consommée
```

Ce rapport n'est pas normalisé par une formule unique ; c'est pourquoi des métriques complémentaires ont été développées (PUE, DCiE, WUE, CUE — voir document 11).

## 5. Bonnes pratiques

Avant une optimisation :

- réaliser un audit énergétique complet (mesure des flux d'énergie IT et infrastructure) ;
- identifier les serveurs sous-utilisés ou obsolètes ;
- cartographier les flux d'air et les points chauds (hot spots).

Après une optimisation :

- vérifier l'impact réel sur les indicateurs énergétiques mesurés (pas seulement estimés) ;
- s'assurer que les gains d'efficacité n'ont pas dégradé la disponibilité ou la résilience du site ;
- documenter les changements pour permettre un suivi dans le temps.

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (à but illustratif uniquement, ne représente aucune installation réelle) :

Un Data Center fictif consomme 100 kWh d'énergie totale par jour, dont 60 kWh sont utilisés directement par les équipements IT et 40 kWh par le refroidissement, l'UPS et l'éclairage. Après une optimisation du refroidissement (containment + relèvement de la température de consigne), la consommation d'infrastructure passe à 25 kWh pour la même charge IT. Le Data Center consomme alors 85 kWh au total pour le même service rendu, soit une réduction d'environ 15 % de la consommation totale — valeur strictement illustrative.

## 7. Limites et précautions

- Les gains d'efficacité varient fortement selon la taille, la localisation climatique, l'âge et l'architecture du Data Center ; aucune valeur générique ne doit être présentée comme universelle.
- L'amélioration de l'efficacité énergétique ne doit jamais se faire au détriment de la sécurité, de la disponibilité ou de la durée de vie des équipements.
- Les chiffres cités dans ce document (hors exemples pédagogiques explicitement marqués) proviennent de synthèses de sources publiques et doivent être vérifiés auprès de la source originale pour tout usage réglementaire ou contractuel.

## 8. Application à GreenDC Advisor

Ce document sert de base de connaissances générale pour qu'un assistant RAG puisse :

- expliquer à l'utilisateur les postes de consommation d'un Data Center et leur importance relative ;
- orienter une analyse de terrain (quels équipements auditer en priorité) ;
- proposer des pistes générales d'amélioration avant d'entrer dans le détail des métriques (PUE, cooling) traitées dans d'autres documents du corpus ;
- contextualiser les recommandations de dimensionnement PV (document 06) par rapport au profil de consommation réel du Data Center.

## 9. Sources et références

- U.S. Department of Energy (DOE) / Federal Energy Management Program (FEMP) — *Best Practices Guide for Energy-Efficient Data Center Design*. Disponible sur : https://www.energy.gov/cmei/femp/articles/best-practices-guide-energy-efficient-data-center-design
- ASHRAE — Datacom Series (référence générale sur les pratiques de conception thermique et énergétique des Data Centers).

Ce document est une reformulation pédagogique ; il ne reproduit pas le contenu intégral des sources citées. Pour toute exigence technique précise, se référer directement aux documents originaux.
