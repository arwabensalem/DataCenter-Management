# Data Center Sustainability Metrics

## 1. Introduction

Au-delà du PUE (détaillé au document 02), plusieurs autres indicateurs ont été développés par des organismes tels que The Green Grid, l'ASHRAE, le DOE et l'ISO pour évaluer la durabilité d'un Data Center sous différents angles : consommation d'eau, émissions carbone, efficacité du refroidissement. Ce document en présente une synthèse pédagogique.

## 2. Définitions

- **PUE (Power Usage Effectiveness)** : voir document 02.
- **WUE (Water Usage Effectiveness)** : indicateur mesurant la consommation d'eau d'un Data Center rapportée à l'énergie consommée par l'IT.
- **CUE (Carbon Usage Effectiveness)** : indicateur mesurant les émissions de carbone associées à l'énergie totale du Data Center, rapportées à l'énergie consommée par l'IT.
- **ERE (Energy Reuse Effectiveness)** : indicateur mesurant la part d'énergie récupérée et réutilisée (par exemple pour le chauffage d'un bâtiment voisin) par rapport à l'énergie totale du Data Center.
- **Utilisation IT** : proportion de la capacité de calcul, de stockage ou réseau effectivement mobilisée par les charges de travail (voir document 04).

## 3. Concepts principaux

### 3.1 WUE — Water Usage Effectiveness

**Définition** : rapport entre l'eau totale consommée par le Data Center (notamment pour le refroidissement par voie humide, tours de refroidissement) et l'énergie consommée par les équipements IT.

**Formule (concept publié par The Green Grid) :**

```
WUE = Eau consommée sur site (litres) / Énergie IT (kWh)
```

**Unité** : litres par kilowattheure (L/kWh).

**Interprétation** : un WUE plus faible indique une consommation d'eau plus faible rapportée à l'énergie IT utilisée. Ce ratio est particulièrement pertinent dans les régions où la ressource en eau est limitée, ce qui est un enjeu notable pour la Tunisie sans que ce document ne fournisse de valeur chiffrée officielle sur la disponibilité en eau du pays.

**Limites** : le WUE dépend fortement de la technologie de refroidissement choisie (le refroidissement à eau évaporative réduit la consommation électrique mais augmente la consommation d'eau ; le refroidissement à air ou le free cooling sec réduisent la consommation d'eau mais peuvent être moins efficaces énergétiquement selon le climat).

**EXEMPLE PÉDAGOGIQUE** : un Data Center fictif consommant 5 000 litres d'eau pour 10 000 kWh d'énergie IT aurait un WUE de 0,5 L/kWh (valeur strictement illustrative).

### 3.2 CUE — Carbon Usage Effectiveness

**Définition** : rapport entre les émissions totales de CO2 associées à l'énergie du Data Center et l'énergie consommée par les équipements IT.

**Formule (concept publié par The Green Grid) :**

```
CUE = Émissions totales de CO2 (kg) / Énergie IT (kWh)
```

**Unité** : kg CO2 / kWh.

**Interprétation** : le CUE combine le PUE et le facteur d'émission du mix électrique utilisé (voir document 10). Un Data Center peut avoir un PUE identique à un autre mais un CUE très différent si les mix électriques diffèrent (par exemple si l'un utilise davantage d'électricité renouvelable, comme le photovoltaïque).

**Limites** : le CUE dépend directement de la fiabilité et de l'actualité du facteur d'émission utilisé (voir les précautions détaillées au document 10) ; aucune valeur de facteur d'émission n'est fournie comme référence universelle dans ce corpus.

**EXEMPLE PÉDAGOGIQUE** : si un facteur d'émission fictif de 0,4 kg CO2/kWh est appliqué à un PUE fictif de 1,6, le CUE indicatif serait d'environ 0,64 kg CO2/kWh d'IT (calcul strictement illustratif, ne représentant aucune donnée officielle).

### 3.3 ERE — Energy Reuse Effectiveness

**Définition** : indicateur mesurant la part d'énergie récupérée hors du Data Center (par exemple pour le chauffage de locaux adjacents) par rapport à l'énergie totale consommée.

**Formule (concept publié par The Green Grid) :**

```
ERE = (Énergie totale du Data Center − Énergie réutilisée) / Énergie IT
```

**Interprétation** : un ERE inférieur au PUE témoigne d'une récupération effective d'énergie ; un ERE égal au PUE signifie qu'aucune énergie n'est réutilisée.

**Limites** : la récupération de chaleur nécessite une infrastructure et des débouchés adaptés (réseau de chaleur, bâtiment voisin), rarement présents dans tous les contextes.

### 3.4 Autres dimensions de durabilité

- **Utilisation IT** : un taux d'utilisation faible dégrade la performance de l'ensemble des métriques rapportées à l'énergie IT, car le dénominateur (énergie IT utile) reste faible malgré une infrastructure de support dimensionnée pour une charge plus importante.
- **Efficacité du refroidissement** : peut être approchée par la part de l'énergie totale consacrée au refroidissement (voir document 03), qui influence directement le PUE.
- **Consommation d'eau** : voir WUE ci-dessus.
- **Émissions carbone** : voir CUE ci-dessus et document 10.

## 4. Méthodes / formules

Voir formules détaillées ci-dessus pour WUE, CUE et ERE. Pour le PUE, voir document 02.

## 5. Bonnes pratiques

- Suivre plusieurs indicateurs de façon complémentaire plutôt qu'un seul (le PUE seul ne renseigne pas sur l'eau ni le carbone).
- Adapter le choix des indicateurs prioritaires au contexte local (par exemple, accorder une attention particulière au WUE dans les régions à ressource en eau limitée, ce qui est pertinent pour un pays comme la Tunisie sans que cela ne constitue une donnée officielle chiffrée dans ce document).
- Documenter systématiquement la méthodologie et le périmètre de calcul de chaque indicateur pour permettre des comparaisons dans le temps.

## 6. Exemple pédagogique

Voir exemples pédagogiques par métrique ci-dessus (WUE et CUE).

## 7. Limites et précautions

- Ces indicateurs sont complémentaires et non substituables les uns aux autres ; aucun ne doit être utilisé isolément pour juger de la performance globale d'un Data Center.
- Les valeurs numériques utilisées dans les exemples de ce document sont toutes fictives et pédagogiques.
- Le CUE et le WUE dépendent de données externes (facteur d'émission, disponibilité en eau) qui doivent être vérifiées et actualisées auprès de sources officielles.

## 8. Application à GreenDC Advisor

Ce document permet à l'assistant RAG de :

- présenter à l'utilisateur un panorama complet des indicateurs de durabilité au-delà du seul PUE ;
- mettre en avant la pertinence particulière du WUE dans le contexte tunisien, marqué par une ressource en eau limitée (sans fournir de statistique officielle non vérifiée) ;
- articuler le CUE avec les résultats du dimensionnement PV (document 06) et le calcul de CO2 évité (document 10) pour évaluer l'impact global d'un scénario de décarbonation.

## 9. Sources et références

- The Green Grid — concepts de PUE, WUE, CUE et ERE (organisme à l'origine de ces métriques, largement repris par l'industrie).
- U.S. Department of Energy (DOE) / FEMP — *Best Practices Guide for Energy-Efficient Data Center Design*.
- ASHRAE — Datacom Series.
- ISO/IEC 30134 (série de normes sur les indicateurs de performance des Data Centers, dont la partie 2 concerne spécifiquement le PUE — voir document 02).

Ce document est une synthèse pédagogique reformulée ; il ne reproduit pas le contenu intégral des normes ISO/IEC 30134 ni des publications originales de The Green Grid.
