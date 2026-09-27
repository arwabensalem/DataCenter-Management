# Photovoltaic System Sizing

## 1. Introduction

Le dimensionnement d'un système photovoltaïque consiste à déterminer la puissance installée, le nombre de panneaux et la configuration technique permettant de répondre à un objectif de couverture énergétique, dans les limites imposées par la surface disponible, le budget et les contraintes techniques de raccordement. Ce document présente une méthodologie pédagogique générale de dimensionnement, complémentaire des exigences techniques tunisiennes détaillées dans le document 07.

## 2. Définitions

- **Consommation cible** : énergie annuelle ou mensuelle consommée par le site à alimenter, estimée à partir de factures historiques ou de mesures.
- **Puissance installée (kWc)** : somme des puissances crête de tous les modules photovoltaïques installés.
- **Surface disponible** : surface exploitable pour l'installation des modules (toiture, ombrière, terrain), en tenant compte des contraintes d'ombrage, d'orientation et de structure porteuse.
- **Taux de couverture énergétique** : part de la consommation totale du site couverte par la production photovoltaïque.
- **ROI (Return on Investment)** : indicateur économique mesurant le temps ou le taux de rendement nécessaire pour récupérer l'investissement initial grâce aux économies ou revenus générés.
- **Période d'amortissement (payback period)** : durée nécessaire pour que les économies cumulées égalent l'investissement initial.

## 3. Concepts principaux

### 3.1 Objectifs du dimensionnement

Le dimensionnement vise à définir une puissance PV cohérente avec :

- la consommation du site (pour éviter un surdimensionnement inutile en l'absence de valorisation de l'excédent, ou un sous-dimensionnement qui limiterait les bénéfices) ;
- la surface disponible et son exploitabilité réelle (ombrage, orientation, structure) ;
- les contraintes de raccordement au réseau (puissance maximale autorisée, voir document 07 pour le cas tunisien) ;
- le budget disponible et les objectifs économiques du porteur de projet.

### 3.2 Étapes générales

1. **Estimation de la consommation** : à partir de l'historique de facturation électrique ou de mesures directes, idéalement avec un profil de consommation horaire ou au moins mensuel.
2. **Estimation du productible PV** : à partir des données d'irradiation locale, de l'orientation et de l'inclinaison envisagées (voir document 05).
3. **Détermination du nombre de panneaux et de la puissance installée**, en tenant compte de la surface disponible.
4. **Estimation de la couverture énergétique** : part de la consommation couverte par la production PV, et part restante à couvrir par le réseau.
5. **Estimation économique** : coût d'investissement, économies attendues, ROI et période d'amortissement.

### 3.3 Couverture énergétique et dépendance réseau

Une installation PV ne couvre généralement pas 100 % de la consommation d'un site fonctionnant en continu (comme un Data Center), en raison du décalage entre les heures de production solaire (journée) et les besoins de consommation (souvent continus 24h/24 pour un Data Center). La part non couverte reste fournie par le réseau, sauf recours à un système de stockage.

## 4. Méthodes / formules

**Estimation simplifiée du nombre de panneaux :**

```
Nombre de panneaux ≈ Puissance installée souhaitée (kWc) / Puissance unitaire d'un panneau (kWc)
```

**Estimation simplifiée de la couverture énergétique :**

```
Taux de couverture (%) ≈ (Production PV utilisée localement / Consommation totale du site) × 100
```

**Estimation simplifiée du ROI / période d'amortissement :**

```
Période d'amortissement (années) ≈ Coût d'investissement initial / Économies annuelles générées
```

Ces formules sont des approximations pédagogiques simplifiées. Une étude réelle doit intégrer la variabilité horaire et saisonnière de la production et de la consommation, ainsi que la dégradation progressive des modules dans le temps.

## 5. Bonnes pratiques

- Baser l'estimation de consommation sur des données réelles (factures, compteurs) plutôt que sur des hypothèses génériques.
- Croiser le profil horaire de consommation du site avec le profil horaire de production PV pour estimer plus précisément le taux d'autoconsommation réel (un Data Center fonctionnant en continu a un profil différent d'un bâtiment tertiaire classique).
- Prendre en compte la dégradation annuelle typique des modules dans les projections à long terme.
- Ne jamais présenter un dimensionnement simplifié comme un dossier technique final : une étude détaillée (simulation logicielle, visite de site) reste nécessaire avant investissement.

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (valeurs entièrement fictives, à but illustratif uniquement — ne représente aucune donnée réelle tunisienne ou d'un Data Center réel) :

Un site fictif consomme 50 000 kWh/an. Une installation PV fictive de 30 kWc produit, selon une hypothèse pédagogique, environ 45 000 kWh/an (voir méthode de calcul en document 05). En supposant qu'environ 60 % de cette production soit effectivement autoconsommée en raison du décalage entre les heures de production et de consommation, l'autoconsommation réelle serait d'environ 27 000 kWh/an, soit un taux de couverture d'environ 54 % de la consommation totale du site (EXEMPLE PÉDAGOGIQUE — valeurs fictives, méthode simplifiée).

## 7. Limites et précautions

- Cette méthodologie simplifiée ne remplace pas une étude technique et économique détaillée réalisée par un bureau d'études ou un installateur qualifié.
- Le taux d'autoconsommation réel dépend fortement du profil horaire de consommation du site, qui doit être mesuré et non supposé.
- Les hypothèses économiques (coût de l'électricité, coût d'investissement, durée de vie des équipements) doivent être actualisées et vérifiées, ce document ne fournissant aucune valeur tarifaire officielle.
- Pour un Data Center, dont la consommation est généralement continue et élevée, le dimensionnement PV doit être pensé en complément du réseau plutôt qu'en remplacement, sauf cas particulier avec stockage important.

## 8. Application à GreenDC Advisor

Ce document constitue la méthodologie centrale que l'assistant RAG peut mobiliser pour :

- guider un utilisateur dans l'estimation d'une puissance PV cohérente avec son Data Center ;
- illustrer le calcul du taux de couverture énergétique et de la dépendance réseau restante ;
- alimenter les simulations de scénarios comparant différentes puissances installées ;
- articuler le dimensionnement avec les contraintes réglementaires tunisiennes détaillées dans le document 07 (limites de puissance, raccordement).

## 9. Sources et références

- National Renewable Energy Laboratory (NREL) — PVWatts (méthodologie générale d'estimation de production). https://pvwatts.nrel.gov/
- Principes généraux de dimensionnement PV largement diffusés dans la littérature technique internationale (NREL, IEA-PVPS).

Ce document présente une méthodologie pédagogique générique ; il ne remplace pas une étude de faisabilité réalisée par un professionnel et ne doit pas être utilisé comme seule base de décision d'investissement.
