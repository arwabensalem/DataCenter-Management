# Photovoltaic Fundamentals

## 1. Introduction

Ce document présente les principes fondamentaux du fonctionnement d'un système photovoltaïque (PV), indépendamment de tout contexte réglementaire national. Il s'appuie sur les ressources publiques du National Renewable Energy Laboratory (NREL), notamment l'outil PVWatts, référence largement utilisée pour l'estimation de production PV.

## 2. Définitions

- **Panneau (module) photovoltaïque** : dispositif composé de cellules photovoltaïques qui convertissent directement le rayonnement solaire en électricité par effet photovoltaïque.
- **Puissance crête (Wc / kWp)** : puissance électrique maximale qu'un module ou une installation peut délivrer dans des conditions d'essai standardisées (irradiance de 1 000 W/m², température de cellule de 25 °C, spectre AM 1.5).
- **Irradiation solaire** : quantité d'énergie solaire reçue par une surface donnée sur une période donnée, généralement exprimée en kWh/m²/jour ou kWh/m²/an.
- **Onduleur (inverter)** : équipement convertissant le courant continu (DC) produit par les modules en courant alternatif (AC) utilisable par les charges ou injectable au réseau.
- **Performance Ratio (PR)** : rapport entre la production réelle d'une installation et sa production théorique idéale dans les mêmes conditions d'irradiation, reflétant l'ensemble des pertes du système.
- **Autoconsommation** : part de l'énergie produite par l'installation PV directement consommée sur place, sans transiter par le réseau.
- **Injection réseau** : part de l'énergie produite non consommée localement et renvoyée vers le réseau électrique.

## 3. Concepts principaux

### 3.1 Fonctionnement d'un système photovoltaïque

Un système PV convertit l'énergie du rayonnement solaire en électricité grâce à l'effet photovoltaïque dans les cellules semi-conductrices des modules. Le courant continu produit est ensuite converti en courant alternatif par un ou plusieurs onduleurs avant d'être consommé localement ou injecté sur le réseau.

### 3.2 Facteurs influençant la production

- **Irradiation et heures d'ensoleillement** : plus l'irradiation reçue est élevée, plus la production potentielle est importante. Le nombre d'heures d'ensoleillement effectif (heures avec un rayonnement exploitable) varie selon la latitude, la saison et les conditions météorologiques locales.
- **Orientation** : dans l'hémisphère nord, une orientation plein sud maximise généralement la production annuelle.
- **Inclinaison** : l'angle d'inclinaison optimal dépend de la latitude du site et de l'objectif recherché (maximiser la production annuelle ou privilégier une saison particulière).
- **Température** : le rendement des cellules photovoltaïques diminue lorsque leur température augmente ; les climats chauds peuvent donc réduire légèrement le rendement instantané des modules, même en présence d'une forte irradiation.

### 3.3 Pertes du système

Une installation PV réelle produit toujours moins que sa production théorique idéale, en raison de pertes cumulées : pertes de température, pertes par salissure/encrassement des modules, pertes de câblage (résistance électrique), pertes de conversion de l'onduleur, pertes par mismatch entre modules, pertes par ombrage partiel, et disponibilité du système (arrêts pour maintenance).

### 3.4 Autoconsommation et injection réseau

Selon la configuration retenue (avec ou sans stockage par batterie), l'énergie produite peut être :

- consommée directement au moment de sa production (autoconsommation instantanée) ;
- stockée temporairement dans une batterie pour un usage différé ;
- injectée sur le réseau électrique lorsque la production excède la consommation locale (selon les règles applicables, voir document 07 pour le cas tunisien).

## 4. Méthodes / formules

**Estimation simplifiée de la production annuelle :**

```
Production annuelle (kWh) ≈ Puissance installée (kWc) × Irradiation annuelle (kWh/m²/an) × Performance Ratio
```

Cette formule est une approximation pédagogique simplifiée. Des outils spécialisés comme PVWatts (NREL) intègrent des modèles plus détaillés tenant compte de l'orientation, de l'inclinaison, de la température, du type de module et de l'onduleur.

**Performance Ratio (PR) :**

```
PR = Production réelle mesurée / Production théorique idéale (dans les mêmes conditions d'irradiation)
```

## 5. Bonnes pratiques

- Utiliser des données d'irradiation locales fiables (bases de données satellitaires ou stations météorologiques) plutôt que des valeurs génériques.
- Prendre en compte les ombrages proches (bâtiments, végétation, obstacles) qui peuvent réduire significativement la production.
- Dimensionner les câbles et l'onduleur en cohérence avec les caractéristiques électriques réelles des modules (voir document 07 pour les aspects techniques de raccordement en Tunisie).
- Prévoir un entretien régulier (nettoyage, inspection) pour limiter les pertes liées à l'encrassement (voir document 08).

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (valeurs fictives, à but illustratif uniquement, ne représente aucune donnée réelle tunisienne) :

Une installation fictive de 10 kWc, avec une irradiation annuelle hypothétique de 1 900 kWh/m²/an et un Performance Ratio hypothétique de 0,80 :

```
Production annuelle ≈ 10 × 1900 × 0,80 = 15 200 kWh/an (EXEMPLE PÉDAGOGIQUE)
```

Cette valeur est purement illustrative de la méthode de calcul ; elle ne doit en aucun cas être utilisée comme référence de production réelle pour la Tunisie ou tout autre site.

## 7. Limites et précautions

- Toute estimation simplifiée de production doit être considérée comme une approximation ; une étude détaillée (simulation logicielle, mesures sur site) reste nécessaire pour un dimensionnement fiable.
- Les données d'irradiation utilisées doivent être clairement sourcées et datées.
- Les valeurs de Performance Ratio varient selon la qualité des équipements, la maintenance et les conditions climatiques locales ; aucune valeur ne doit être présentée comme universelle.

## 8. Application à GreenDC Advisor

Ce document permet à l'assistant RAG de :

- expliquer les principes physiques et techniques de base du photovoltaïque à un utilisateur non spécialiste ;
- fournir la méthode générale d'estimation de production utilisée en amont du dimensionnement détaillé (document 06) ;
- rappeler l'importance de données d'irradiation locales fiables avant toute simulation de couverture solaire d'un Data Center.

## 9. Sources et références

- National Renewable Energy Laboratory (NREL) — Outil PVWatts. https://pvwatts.nrel.gov/
- NREL — publications générales sur les fondamentaux du photovoltaïque (System Advisor Model, ressources pédagogiques publiques).

Ce document est une synthèse pédagogique reformulée à partir de ressources publiques du NREL ; il ne reproduit pas le code ni les algorithmes internes de l'outil PVWatts.
