# Photovoltaic in Tunisia

## 1. Introduction

Ce document est un document **central** pour GreenDC Advisor : il synthétise les exigences techniques et le contexte réglementaire tunisien applicables aux installations photovoltaïques (CPV — Centrales Photovoltaïques) raccordées au réseau électrique national basse tension (BT). Il s'appuie sur le **Référentiel technique des installations photovoltaïques raccordées au réseau électrique national basse tension**, élaboré dans le cadre des travaux de mise à jour du référentiel technique BT par les représentants de la Société Tunisienne de l'Électricité et du Gaz (STEG), publié par l'Agence Nationale pour la Maîtrise de l'Énergie (ANME).

**Avertissement méthodologique important** : seules les informations effectivement présentes dans la source consultée sont reprises ici. Les exigences techniques réglementaires sont explicitement distinguées des recommandations et des informations pédagogiques complémentaires. En cas de doute ou d'évolution réglementaire, se référer au texte source à jour et aux textes légaux tunisiens cités.

## 2. Définitions (issues du référentiel)

- **Basse tension (BT)** : réseau électrique dont la tension est inférieure ou égale à 1000 V en courant alternatif ou 1500 V en courant continu.
- **Centrale photovoltaïque (CPV)** : installation de production d'électricité à partir de l'énergie solaire, incluant les modules, onduleurs, protections et raccordement au réseau.
- **Producteur** : personne physique ou morale responsable de la production d'électricité à partir d'une installation photovoltaïque.
- **Puissance souscrite** : puissance maximale demandée par l'utilisateur et contractualisée avec la STEG.
- **Point de raccordement du producteur** : point physique où l'installation photovoltaïque est connectée au réseau de la STEG.
- **BESS (Battery Energy Storage System)** : système de stockage d'énergie par batterie, composé d'unités de batteries, d'un onduleur (de batterie ou hybride), d'un système de gestion de batterie (BMS), de protections électriques et d'un système de supervision.
- **Taux d'autoconsommation** : part de l'énergie produite par l'installation consommée directement sur place.
- **Taux d'autoproduction** : part de la consommation totale couverte par la production photovoltaïque propre.

## 3. Concepts principaux — Contexte réglementaire tunisien

### 3.1 Références réglementaires (EXIGENCE RÉGLEMENTAIRE)

Le référentiel technique s'appuie explicitement sur les textes suivants :

- Loi n° 2015-12 du 11 mai 2015, relative à la production d'électricité à partir des énergies renouvelables ;
- Décret n° 64-9 du 17 janvier 1964, portant approbation du cahier des charges relatif à la fourniture de l'énergie électrique sur l'ensemble du territoire de la République ;
- Décret gouvernemental n° 2016-1123 du 24 août 2016, fixant les conditions et modalités de réalisation des projets de production et de vente d'électricité à partir des énergies renouvelables ;
- Arrêté de la ministre de l'énergie, des mines et des énergies renouvelables du 9 février 2017, portant approbation du cahier des charges relatif aux exigences techniques de raccordement et d'évacuation de l'énergie produite à partir des installations d'énergies renouvelables raccordées sur le réseau basse tension.

### 3.2 Rôle de la STEG

La STEG (Société Tunisienne de l'Électricité et du Gaz) est le distributeur national d'électricité. Selon le référentiel (EXIGENCE RÉGLEMENTAIRE) :

- les CPV en régime d'autoconsommation sont raccordées au même point de raccordement que les installations électriques du producteur, en basse tension ;
- la STEG définit les critères d'acceptation techniques des onduleurs et des systèmes de conversion de puissance (PCS) ;
- la STEG réalise une étude de raccordement pour toute CPV dont la puissance dépasse 20 kVA, pouvant conduire à une exigence de réduction de puissance ou de renforcement du réseau à la charge du producteur ;
- le compteur d'énergie existant est remplacé par un compteur bidirectionnel pour permettre la mesure de l'énergie injectée et soutirée.

### 3.3 Rôle de l'ANME

Le document source précise que le référentiel est publié par l'Agence Nationale pour la Maîtrise de l'Énergie (ANME) et que **tous les modules photovoltaïques d'une CPV doivent être homologués par l'ANME** (EXIGENCE RÉGLEMENTAIRE explicitement mentionnée dans la source).

### 3.4 Limites de puissance des installations raccordées en basse tension (EXIGENCE RÉGLEMENTAIRE)

D'après le référentiel :

- la puissance d'une CPV raccordée au réseau ne doit pas dépasser la puissance souscrite par le client ;
- la puissance maximale d'une CPV raccordable en triphasé sur le réseau BT est de 200 kVA ;
- pour un client alimenté en **monophasé** : la puissance maximale de l'onduleur ne doit pas dépasser la puissance souscrite **et** ne doit pas dépasser 6 kVA ;
- pour un client alimenté en **triphasé**, avec une installation photovoltaïque de puissance supérieure à 6 kVA : l'utilisation d'onduleurs triphasés est obligatoire, et la puissance maximale AC de l'installation ne doit pas dépasser la puissance souscrite.

### 3.5 Caractéristiques du réseau BT tunisien (EXIGENCE RÉGLEMENTAIRE)

- Le schéma de liaison à la terre adopté est le schéma TT.
- La tension nominale du réseau est de 230/400 V, avec une tolérance de ± 10 %.
- La fréquence nominale est de 50 Hz, avec une tolérance de ± 1 %.
- Tout onduleur photovoltaïque doit être conçu pour fonctionner dans une plage de fréquence de [47,5 Hz ; 52 Hz] et une plage de tension de [-15 % Un ; +10 % Un].

### 3.6 Exigences techniques minimales pour les onduleurs (EXIGENCE RÉGLEMENTAIRE)

Selon le référentiel, les onduleurs doivent notamment respecter :

- une tension nominale de 230 V (monophasé) ou 400 V (triphasé) ;
- un facteur de puissance réglable entre -0,8 (inductif) et +0,8 (capacitif) ;
- un taux de distorsion harmonique totale en courant ne dépassant pas 4 % ;
- un indice de protection (IP) au moins égal à IP 65 ;
- une température de fonctionnement comprise au minimum entre -10 °C et 50 °C ;
- une déconnexion en 0,3 seconde en cas de courant résiduel permanent dépassant 300 mA (pour les onduleurs ≤ 30 kVA) ou 10 mA/kVA (pour les onduleurs > 30 kVA) ;
- une protection de découplage assurant la déconnexion automatique en cas de perturbation ou de coupure du réseau, certifiée conforme à des normes reconnues (par exemple VDE-AR-N 4105, CEI 62109-1/2).

### 3.7 Dossier d'acceptation des onduleurs et PCS par la STEG (EXIGENCE RÉGLEMENTAIRE)

Le raccordement d'un onduleur ou d'un système de conversion de puissance (PCS) au réseau BT n'est possible qu'après acceptation par la STEG, sur la base d'un dossier comprenant notamment : la fiche technique de l'équipement, les certificats de conformité aux normes applicables (par exemple VDE-AR-N 4105, CEI 62109-1/2, directives européennes CEM et basse tension), et, pour les PCS, des informations complémentaires (rapports de tests, plan de maintenance, schémas unifilaires).

### 3.8 Structure porteuse et implantation (EXIGENCE RÉGLEMENTAIRE / RECOMMANDATION)

Le référentiel précise des règles de conception mécanique des structures porteuses (résistance au vent jusqu'à une vitesse de 120 km/h, matériaux recommandés — aluminium anodisé ou acier galvanisé à chaud selon la norme ISO 1461:2009, vérification par un bureau de contrôle agréé), ainsi que des recommandations d'orientation (plein sud, azimut 0°) et d'inclinaison (30° par rapport à l'horizontale) pour une production optimale sur l'année (RECOMMANDATION, à adapter selon les contraintes du site).

### 3.9 Batteries de stockage (BESS) — cadre optionnel (EXIGENCE RÉGLEMENTAIRE lorsque le BESS est installé)

Le référentiel intègre des exigences pour l'incorporation facultative d'un système de stockage par batterie (BESS), destiné à améliorer l'autoconsommation. Les batteries doivent être conformes à des normes internationales telles que CEI 62619 (sécurité des batteries lithium-ion stationnaires), CEI 62620 (performance et essais) et CEI 61427 (batteries pour systèmes PV stationnaires), en complément des prescriptions de l'arrêté du 9 février 2017 et de la réglementation locale de sécurité incendie et de gestion des déchets dangereux.

## 4. Méthodes / formules

Le référentiel fournit des méthodes de dimensionnement technique détaillées (hors du périmètre économique traité au document 06), notamment :

- calcul de la distance de retrait par rapport à un obstacle proche, en fonction de la hauteur solaire locale ;
- calcul du nombre de modules en série et en parallèle compatibles avec la plage de tension et de courant d'un onduleur donné ;
- calcul du rendement global d'un système de stockage selon le type de couplage (DC, AC ou mixte) : `η_système = η_chargeur MPPT × η_batterie × η_onduleur batterie` (couplage DC).

Ces formules techniques détaillées relèvent de l'ingénierie de dimensionnement électrique et doivent être appliquées par un installateur qualifié en tenant compte des fiches techniques réelles des équipements.

## 5. Bonnes pratiques (recommandations issues du référentiel et de la pratique professionnelle)

- Vérifier l'homologation ANME des modules avant tout achat.
- Constituer un dossier technique complet avant de solliciter l'acceptation de la STEG (fiches techniques, certificats de conformité).
- Anticiper l'étude de raccordement STEG pour toute installation dépassant 20 kVA, qui peut allonger les délais du projet.
- Faire réaliser les vérifications de structure porteuse par un bureau de contrôle agréé.
- Prévoir, en cas d'ombrage jugé permanent, l'engagement signé requis par le référentiel dans le dossier technique.

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (calcul illustratif présent dans la méthodologie du référentiel, à ne pas confondre avec une donnée officielle propre à un projet réel) : pour une distance de retrait par rapport à un obstacle de hauteur h = 1 m, avec une élévation des modules e = 0,2 m, et une hauteur solaire α = 26°, la formule `d = (h - e) / tan(α)` donne une distance de retrait d'environ 1,64 mètre. Ce calcul est une illustration de méthode et doit être refait pour chaque site avec ses paramètres réels.

## 7. Limites et précautions

- Ce document reflète le contenu du référentiel technique tel que consulté à la date indiquée dans `metadata.json` ; toute évolution réglementaire ultérieure doit être vérifiée auprès de la STEG et de l'ANME.
- Les seuils techniques cités (puissances maximales, plages de tension/fréquence, IP, THD, etc.) sont des exigences issues de la source et ne doivent pas être extrapolées à d'autres pays ou à des installations de puissance différente (moyenne/haute tension) sans vérification.
- Les procédures administratives précises (délais, formulaires) ne sont pas détaillées dans ce document et doivent être vérifiées directement auprès de la STEG et de l'ANME.
- Ce document ne traite pas des installations d'autoproduction raccordées en moyenne ou haute tension, hors périmètre de la source utilisée.

## 8. Application à GreenDC Advisor

Ce document est la référence principale que l'assistant RAG doit mobiliser pour toute question portant sur :

- la faisabilité réglementaire d'un projet PV pour un Data Center tunisien raccordé en basse tension (limite de puissance, homologation ANME, acceptation STEG) ;
- les exigences techniques minimales à intégrer dans un dimensionnement (document 06) pour qu'il reste conforme au cadre tunisien ;
- l'orientation d'un utilisateur vers la STEG ou l'ANME pour toute démarche administrative précise, l'assistant devant préciser qu'il fournit une synthèse et non un conseil réglementaire opposable.

## 9. Sources et références

**Source principale (réglementaire tunisienne) :**
- ANME / STEG — *Référentiel technique des installations photovoltaïques raccordées au réseau électrique national basse tension* (version consultée en ligne). URL de référence communiquée : https://www.anme.tn/sites/default/files/2025-03/referentiel-technique-des-installations-photovoltaiques-raccordees-au-reseau-electrique-national-basse-tension.pdf

**Textes légaux cités par la source :**
- Loi n° 2015-12 du 11 mai 2015 relative à la production d'électricité à partir des énergies renouvelables ;
- Décret n° 64-9 du 17 janvier 1964 ;
- Décret gouvernemental n° 2016-1123 du 24 août 2016 ;
- Arrêté du 9 février 2017 portant approbation du cahier des charges relatif aux exigences techniques de raccordement et d'évacuation de l'énergie produite à partir des installations d'énergies renouvelables raccordées sur le réseau basse tension.

Ce document reformule et résume le contenu du référentiel technique ; il n'en reproduit pas l'intégralité et ne se substitue pas à la consultation du document officiel à jour auprès de l'ANME et de la STEG pour toute décision réglementaire ou contractuelle.
