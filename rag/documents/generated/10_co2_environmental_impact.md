# CO2 and Environmental Impact

## 1. Introduction

Ce document présente les principes généraux permettant d'estimer l'impact environnemental (émissions de CO2) associé à la consommation électrique d'un Data Center, ainsi que le principe de réduction des émissions grâce au recours au photovoltaïque. **Aucune valeur de facteur d'émission n'est présentée ici comme une donnée universelle ou officielle pour la Tunisie** ; ce document explique le principe méthodologique, à appliquer avec des données officielles vérifiées au moment de l'analyse.

## 2. Définitions

- **Facteur d'émission (Emission Factor)** : quantité de CO2 (ou équivalent CO2) émise par unité d'énergie électrique produite ou consommée, généralement exprimée en kg CO2/kWh. Ce facteur dépend fortement du mix électrique du pays ou de la zone considérée (part de gaz, de charbon, de renouvelable, etc.) et évolue dans le temps.
- **CO2 évité** : quantité d'émissions de CO2 qui n'ont pas eu lieu grâce à la substitution d'une partie de la consommation réseau par une production locale renouvelable (par exemple photovoltaïque).
- **Facteur d'émission générique** : valeur moyenne ou par défaut utilisée en l'absence de donnée officielle spécifique, à utiliser avec prudence et toujours en précisant son origine et ses limites.
- **Facteur d'émission officiel** : valeur publiée par une autorité nationale ou un organisme reconnu (par exemple un opérateur électrique national, une agence de l'énergie ou un organisme international), pour une année et un périmètre donnés.

## 3. Concepts principaux

### 3.1 Relation entre consommation électrique et émissions

La production d'électricité génère des émissions de gaz à effet de serre dont l'intensité dépend directement du mix énergétique utilisé (part de sources fossiles vs renouvelables). Un même niveau de consommation électrique peut donc correspondre à des niveaux d'émissions très différents selon le pays, la région, et l'année considérée, en raison de l'évolution du mix électrique dans le temps.

### 3.2 Principe de calcul général

Le calcul général des émissions associées à une consommation électrique repose sur le principe suivant :

```
CO2 (kg) = Énergie consommée (kWh) × Facteur d'émission (kg CO2/kWh)
```

Ce principe est une base méthodologique reconnue internationalement (utilisée notamment dans les référentiels de type GHG Protocol pour le calcul des émissions dites "Scope 2" liées à l'électricité achetée), mais son application nécessite un facteur d'émission fiable, daté et propre au périmètre étudié.

### 3.3 CO2 évité grâce au photovoltaïque

Lorsqu'une installation photovoltaïque remplace une partie de la consommation qui aurait autrement été fournie par le réseau, les émissions "évitées" peuvent être estimées en appliquant le même principe à l'énergie autoconsommée d'origine PV :

```
CO2 évité (kg) = Énergie PV autoconsommée (kWh) × Facteur d'émission du réseau évité (kg CO2/kWh)
```

Cette estimation reste une approximation, car le facteur d'émission "marginal" du réseau (c'est-à-dire la source d'électricité réellement évitée à un instant donné) peut différer du facteur d'émission moyen annuel.

### 3.4 Importance de préciser le pays et l'année

Le facteur d'émission d'un réseau électrique évolue dans le temps (évolution du mix énergétique, ajout de capacités renouvelables ou fossiles) et diffère fortement d'un pays à l'autre. Toute estimation de CO2 doit donc systématiquement préciser :

- le pays ou la zone électrique concernée ;
- l'année de référence du facteur d'émission utilisé ;
- la source officielle du facteur d'émission (organisme national de l'énergie, opérateur électrique, ou organisme international reconnu).

### 3.5 Différence entre facteur générique et facteur officiel

Des facteurs d'émission "génériques" ou moyens mondiaux/régionaux circulent parfois dans la littérature ou les médias sans référence précise. Ces valeurs génériques ne doivent pas être confondues avec un facteur d'émission officiel publié pour un pays et une année donnés, qui seul doit être utilisé pour toute analyse rigoureuse ou communication officielle.

## 4. Méthodes / formules

**Formule générale (rappel) :**

```
CO2 = Énergie × Facteur d'émission
```

**Précaution méthodologique** : le facteur d'émission doit toujours être accompagné de sa source, de son année de référence et du pays/zone concerné. En l'absence de facteur officiel vérifié pour la Tunisie disponible dans ce corpus, toute estimation de CO2 pour un Data Center tunisien doit être présentée comme une **estimation basée sur une hypothèse à vérifier**, et non comme une donnée officielle.

## 5. Bonnes pratiques

- Toujours indiquer la source, l'année et le périmètre géographique du facteur d'émission utilisé dans un calcul.
- Distinguer clairement, dans toute communication, une estimation basée sur une hypothèse d'un chiffre officiel vérifié.
- Mettre à jour les facteurs d'émission utilisés à mesure que de nouvelles données officielles sont publiées.
- Ne jamais présenter un facteur d'émission générique comme une valeur universelle applicable à tous les pays.

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (valeur de facteur d'émission fictive utilisée uniquement pour illustrer la méthode de calcul — **ne constitue en aucun cas une valeur officielle tunisienne**) :

En supposant, à titre d'exemple pédagogique uniquement, un facteur d'émission hypothétique de 0,50 kg CO2/kWh (valeur fictive), une installation PV produisant 15 000 kWh/an autoconsommés permettrait, selon cette hypothèse fictive, d'éviter environ :

```
CO2 évité ≈ 15 000 × 0,50 = 7 500 kg CO2/an (EXEMPLE PÉDAGOGIQUE — valeur de facteur d'émission fictive)
```

Cette valeur est strictement illustrative de la méthode de calcul. Pour toute estimation réelle concernant la Tunisie, il convient d'utiliser un facteur d'émission officiel et à jour, publié par une source reconnue (par exemple un organisme national de l'énergie ou un organisme international spécialisé), et non la valeur fictive utilisée ici.

## 7. Limites et précautions

- **Aucune valeur de facteur d'émission (par exemple 0,55 kg CO2/kWh ou toute autre valeur) n'est présentée dans ce document comme une donnée universelle ou officielle.** Toute valeur numérique utilisée dans les exemples est explicitement fictive et pédagogique.
- Les facteurs d'émission varient significativement d'un pays à l'autre et dans le temps ; l'utilisation d'une valeur non vérifiée peut conduire à des conclusions erronées.
- Le calcul du CO2 évité par une installation PV dépend d'hypothèses sur le mix électrique marginal évité, qui peuvent différer du mix moyen annuel.
- Pour toute communication officielle ou réglementaire sur les émissions évitées, il est recommandé de consulter les méthodologies reconnues (par exemple le GHG Protocol pour le Scope 2) et les données officielles tunisiennes lorsqu'elles sont disponibles et vérifiées.

## 8. Application à GreenDC Advisor

Ce document permet à l'assistant RAG de :

- expliquer la méthode générale de calcul des émissions de CO2 associées à la consommation électrique d'un Data Center ;
- refuser explicitement de présenter une valeur de facteur d'émission non vérifiée comme une donnée officielle tunisienne, et le signaler clairement à l'utilisateur ;
- estimer, à titre d'exemple pédagogique uniquement et avec les avertissements appropriés, le potentiel de CO2 évité par un scénario de couverture PV donné (document 06), en insistant sur la nécessité d'utiliser un facteur d'émission officiel pour toute conclusion définitive.

## 9. Sources et références

- Principe méthodologique général du calcul des émissions liées à l'électricité (Scope 2), largement documenté dans la littérature publique internationale sur le bilan carbone (par exemple le cadre du GHG Protocol).
- Aucune source officielle tunisienne de facteur d'émission électrique n'est intégrée dans ce document ; toute utilisation réelle doit s'appuyer sur une donnée officielle vérifiée au moment de l'analyse, à rechercher auprès des organismes nationaux compétents (par exemple l'ANME) ou d'organismes internationaux reconnus.

Ce document présente une méthodologie générale et ne fournit aucune donnée chiffrée officielle ; toute valeur numérique figurant dans les exemples est explicitement fictive et pédagogique.
