# Green Data Center Decision Making

## 1. Introduction

Ce document synthétise une démarche d'analyse d'un Data Center en vue de recommandations d'optimisation énergétique et de dimensionnement photovoltaïque. Il propose des **arbres de décision pédagogiques**, destinés à structurer le raisonnement d'un assistant d'aide à la décision comme GreenDC Advisor. **Ces règles sont des principes d'aide à la décision et non des décisions automatiques universelles** : chaque situation réelle doit être analysée par un expert avant toute décision d'investissement.

## 2. Définitions

- **Arbre de décision pédagogique** : représentation simplifiée d'un enchaînement de questions et de recommandations, destinée à orienter une analyse, sans se substituer à une expertise technique complète.
- **Dépendance réseau** : part de la consommation d'un site couverte par l'électricité fournie par le réseau électrique national plutôt que par une production locale.
- **Simulation** : exercice de modélisation combinant hypothèses de consommation, de production PV et de coûts pour comparer différents scénarios (voir documents 06 et 10).

## 3. Concepts principaux

L'analyse d'un Data Center en vue de son optimisation énergétique et de l'intégration éventuelle du photovoltaïque combine plusieurs axes d'analyse complémentaires :

- la consommation globale et son évolution ;
- le PUE et les indicateurs de durabilité associés (document 02, document 11) ;
- l'état et l'utilisation des équipements IT (document 04) ;
- l'efficacité du refroidissement (document 03) ;
- le potentiel et le dimensionnement photovoltaïque (documents 05, 06, 07) ;
- la couverture solaire atteignable et la dépendance réseau résiduelle ;
- le coût énergétique global et les indicateurs environnementaux (document 10).

Ces axes sont interdépendants : par exemple, une réduction de la consommation IT (consolidation) réduit à la fois le besoin de refroidissement et le dimensionnement PV nécessaire pour atteindre un taux de couverture donné.

## 4. Méthodes / formules — Arbres de décision pédagogiques

**Avertissement** : les règles ci-dessous sont des heuristiques générales destinées à orienter une première analyse. Elles ne remplacent pas un audit technique complet et ne doivent pas être appliquées mécaniquement sans contexte.

### 4.1 Consommation élevée

```
SI consommation élevée (par rapport à l'historique du site ou à des sites comparables)
→ Analyser en priorité les équipements IT (document 04) :
   - Identifier les serveurs sous-utilisés ou obsolètes
   - Évaluer le potentiel de virtualisation/consolidation
   - Vérifier l'état des mécanismes de gestion d'énergie
```

### 4.2 PUE élevé

```
SI PUE élevé (par rapport à l'historique du site)
→ Analyser le refroidissement et l'infrastructure (document 03) :
   - Vérifier la présence d'un containment allée chaude/allée froide
   - Vérifier les consignes de température par rapport aux recommandations ASHRAE
   - Évaluer le potentiel de free cooling selon le climat local
   - Vérifier le rendement des UPS et de la distribution électrique
```

### 4.3 Serveur sous-utilisé

```
SI un ou plusieurs serveurs sont sous-utilisés
→ Envisager la consolidation et/ou la virtualisation (document 04) :
   - Vérifier la compatibilité des charges de travail avec la virtualisation
   - Évaluer l'impact sur la disponibilité et la résilience avant toute action
```

### 4.4 Couverture PV faible

```
SI la couverture PV envisagée est faible par rapport à la consommation du site
→ Analyser la surface disponible et le potentiel PV (documents 05, 06, 07) :
   - Vérifier la surface exploitable réelle (ombrages, orientation, structure porteuse)
   - Vérifier les limites de puissance réglementaires applicables (document 07)
   - Évaluer la pertinence d'un système de stockage (BESS) pour améliorer l'autoconsommation
```

### 4.5 Dépendance réseau élevée

```
SI la dépendance au réseau électrique national est élevée
→ Analyser le potentiel PV et le profil de consommation (documents 05, 06) :
   - Comparer le profil horaire de consommation avec le profil de production PV
   - Étudier la pertinence d'un système de stockage pour couvrir les heures sans soleil
   - Mettre en regard le coût du réseau et le coût d'investissement PV (document 06)
```

## 5. Bonnes pratiques

- Toujours croiser plusieurs axes d'analyse avant de formuler une recommandation (par exemple, ne pas recommander un dimensionnement PV sans avoir d'abord examiné le potentiel de réduction de la consommation IT).
- Prioriser les actions à faible coût et fort impact (audit, consolidation, ajustement du refroidissement) avant les investissements lourds (PV, remplacement d'équipements).
- Présenter systématiquement les hypothèses et les limites de toute simulation à l'utilisateur.
- Adapter les recommandations à l'échelle et au contexte réel du Data Center (un site de petite taille n'a pas les mêmes leviers qu'un site de grande taille).

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (scénario fictif, à but illustratif uniquement) : un Data Center fictif présente une consommation stable mais un PUE jugé élevé par rapport aux Data Centers comparables. L'analyse orientée par l'arbre de décision 4.2 conduit à examiner le refroidissement : absence de containment, consignes de température très basses. Une recommandation de mise en place d'un containment et de relèvement progressif de la consigne est formulée, à valider par une étude technique. En parallèle, un dimensionnement PV préliminaire (document 06) est proposé pour réduire la dépendance réseau restante. Ce scénario est fictif et ne représente aucune installation réelle.

## 7. Limites et précautions

- Les arbres de décision présentés sont des simplifications pédagogiques et ne couvrent pas l'ensemble des cas réels rencontrés en exploitation.
- Une recommandation générée à partir de ces règles doit toujours être présentée comme une **piste d'analyse à approfondir**, et non comme une décision finale ou une garantie de résultat.
- Les interactions entre les différents leviers (IT, refroidissement, PV, stockage) peuvent être complexes et nécessitent parfois une modélisation plus fine qu'une simple règle "SI... ALORS...".
- Toute décision d'investissement doit s'appuyer sur une étude technique et économique détaillée réalisée par des professionnels qualifiés.

## 8. Application à GreenDC Advisor

Ce document constitue le cœur logique de l'assistant RAG GreenDC Advisor pour :

- structurer le raisonnement d'aide à la décision face à une question d'un utilisateur sur l'optimisation d'un Data Center ;
- orienter la récupération des documents pertinents du corpus (consommation → document 04, PUE → documents 02/03, PV → documents 05/06/07, CO2 → document 10) ;
- formuler des recommandations toujours accompagnées d'un rappel des limites et de la nécessité d'une validation experte, conformément à l'esprit de ce corpus documentaire.

## 9. Sources et références

Ce document est une synthèse méthodologique originale élaborée pour les besoins de GreenDC Advisor, s'appuyant sur les concepts et bonnes pratiques présentés dans l'ensemble des autres documents de ce corpus (documents 01 à 11), eux-mêmes sourcés auprès du DOE, de l'ASHRAE, du NREL, de l'ANME/STEG, de l'ISO et de The Green Grid (voir les sections "Sources et références" de chaque document concerné).

Ce document ne constitue pas une source primaire ; il agrège et structure les connaissances des autres documents du corpus à des fins d'aide à la décision.
