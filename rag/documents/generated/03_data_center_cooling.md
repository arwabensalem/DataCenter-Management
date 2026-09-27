# Data Center Cooling

## 1. Introduction

Le refroidissement est généralement le deuxième poste de consommation énergétique d'un Data Center après les équipements IT, et souvent le principal levier d'amélioration du PUE. Ce document présente les concepts fondamentaux du refroidissement des salles informatiques, en s'appuyant sur les publications de référence de l'ASHRAE (American Society of Heating, Refrigerating and Air-Conditioning Engineers).

## 2. Définitions

- **CRAC (Computer Room Air Conditioner)** : unité de climatisation à détente directe (compresseur intégré) dédiée aux salles informatiques.
- **CRAH (Computer Room Air Handler)** : unité de traitement d'air alimentée en eau glacée produite par un groupe froid (chiller) externe.
- **Allée chaude / allée froide (Hot aisle / Cold aisle)** : disposition des baies serveurs de façon à séparer les flux d'air froid soufflé (façade des serveurs) des flux d'air chaud rejeté (arrière des serveurs).
- **Containment** : confinement physique (cloisons, portes) de l'allée chaude ou de l'allée froide pour éviter le mélange des flux d'air chaud et froid.
- **Free cooling** : utilisation de l'air extérieur ou de sources naturelles fraîches pour refroidir la salle, en réduisant ou supprimant le recours à la production frigorifique mécanique.
- **Liquid cooling (refroidissement liquide)** : technique de refroidissement direct des composants (puces, plaques froides) ou immersion des serveurs dans un liquide diélectrique, offrant une meilleure évacuation thermique que l'air pour les charges à forte densité.

## 3. Concepts principaux

### 3.1 Rôle du refroidissement

Le refroidissement a pour fonction de maintenir la température et l'humidité de l'air entrant dans les équipements IT dans une plage compatible avec leur fonctionnement fiable, tout en évacuant la chaleur générée par les composants électroniques.

### 3.2 Gestion des flux d'air (Airflow management)

Une gestion efficace des flux d'air vise à éviter :

- le recirculation de l'air chaud vers l'entrée des serveurs (recirculation) ;
- le mélange (bypass) de l'air froid directement vers le retour sans traverser les équipements ;
- les points chauds locaux (hot spots) dus à une mauvaise répartition de la charge IT.

La disposition en allées chaude/froide, combinée au containment, est une pratique largement recommandée par l'ASHRAE pour réduire ces phénomènes et permettre d'augmenter les températures de consigne sans risque.

### 3.3 Température et humidité

L'ASHRAE publie des recommandations (Thermal Guidelines for Data Processing Environments) définissant des plages recommandées et des plages autorisées de température et d'humidité en entrée d'équipement, différenciées par classes d'équipements (A1 à A4, etc.). Ces plages ont été élargies au fil des révisions successives du guide, permettant des économies d'énergie substantielles par rapport aux pratiques historiques très conservatrices. Les valeurs précises doivent être vérifiées dans l'édition en vigueur du guide ASHRAE, ce document ne les reproduisant pas intégralement pour des raisons de droits d'auteur.

### 3.4 Free cooling

Le free cooling exploite les conditions climatiques extérieures (température, parfois humidité) pour refroidir la salle sans production frigorifique mécanique complète, ou en la complétant partiellement. Il existe plusieurs variantes : free cooling direct (air extérieur introduit directement), indirect (échangeur air/air ou air/eau sans mélange direct de l'air), et free cooling "eau" (utilisation de tours de refroidissement en mode sec lorsque la température extérieure le permet).

### 3.5 Liquid cooling

Face à l'augmentation de la densité de puissance de certains équipements (notamment pour le calcul intensif et l'IA), le refroidissement liquide (direct-to-chip ou immersion) permet une évacuation thermique plus efficace que l'air pour des racks à très forte densité.

## 4. Méthodes / formules

Il n'existe pas de formule unique pour le refroidissement ; l'efficacité de cette fonction est généralement évaluée via sa contribution au PUE (voir document 02) ou via des indicateurs dédiés tels que le CUE et le WUE (voir document 11).

## 5. Bonnes pratiques

- Mettre en place une séparation physique claire entre allées chaudes et allées froides.
- Ajouter un containment (portes, cloisons, plafonds) pour limiter le mélange d'air.
- Relever progressivement les températures de consigne, dans les limites recommandées par l'ASHRAE et les spécifications des équipements installés.
- Étudier la faisabilité du free cooling selon le climat local (nombre d'heures par an où la température extérieure est favorable).
- Positionner les capteurs de température au niveau des entrées d'air des équipements IT plutôt qu'uniquement dans le retour d'air.
- Éliminer les fuites d'air parasites (câblages non obturés, plancher technique mal étanché).

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (à but illustratif uniquement) :

Un Data Center fictif fonctionne avec une consigne de température d'entrée d'air de 18 °C sans containment. Après mise en place d'un containment allée froide et un relèvement progressif de la consigne à 24 °C (dans une plage jugée compatible avec les équipements installés, à valider au cas par cas), l'exploitant observe une réduction significative des heures de fonctionnement des groupes froids. Aucune valeur chiffrée de gain n'est donnée ici, car elle dépendrait fortement du climat et de l'installation réelle.

## 7. Limites et précautions

- Les plages de température et d'humidité recommandées dépendent des équipements installés et doivent être validées avec les spécifications constructeur.
- Le free cooling n'est pertinent que si le climat local présente un nombre d'heures suffisant sous les seuils requis ; en climat chaud comme celui de la Tunisie, son potentiel est plus limité qu'en climat tempéré ou froid, sans que ce document ne fournisse de valeur chiffrée officielle pour la Tunisie.
- Un mauvais refroidissement peut entraîner des arrêts d'équipements par surchauffe, une réduction de la durée de vie du matériel, voire des dommages irréversibles.
- Ce document ne remplace pas une étude thermique réalisée par un bureau d'études spécialisé.

## 8. Application à GreenDC Advisor

Ce document permet à l'assistant RAG de :

- expliquer les principes de base du refroidissement à un utilisateur non spécialiste ;
- orienter les recommandations d'optimisation énergétique (containment, relèvement de température, free cooling) en lien avec le PUE ;
- mettre en perspective la pertinence limitée du free cooling en climat tunisien par rapport à d'autres leviers ;
- contextualiser l'impact du refroidissement dans les simulations de consommation utilisées pour le dimensionnement photovoltaïque.

## 9. Sources et références

- ASHRAE — *Datacom Series* et *Thermal Guidelines for Data Processing Environments* (référence de l'industrie sur les plages de température/humidité et les pratiques de refroidissement). Handbook consultable via : https://handbook.ashrae.org/Handbooks/A23/IP/A23_Ch20/a23_ch20_ip.aspx
- U.S. Department of Energy (DOE) / FEMP — *Best Practices Guide for Energy-Efficient Data Center Design*.

Ce document reformule et résume des concepts publics ; il ne reproduit pas les tableaux de valeurs protégés par le droit d'auteur de l'ASHRAE. Pour toute valeur précise de température ou d'humidité, se référer à l'édition en vigueur des guides ASHRAE.
