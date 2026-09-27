# PV Installation and Maintenance

## 1. Introduction

Ce document présente les principes d'installation, de mise en service et de maintenance des centrales photovoltaïques (CPV) raccordées au réseau basse tension. Il combine des éléments explicitement présents dans le référentiel technique ANME/STEG (chapitres relatifs aux vérifications, essais et maintenance) et des bonnes pratiques internationales de maintenance PV, clairement distinguées.

**Note de traçabilité** : le "Guide pratique d'installation des systèmes photovoltaïques raccordés au réseau électrique national" mentionné comme source secondaire n'a pas pu être vérifié directement à l'URL fournie au moment de la rédaction de ce document. Les éléments réglementaires tunisiens présentés ici proviennent donc du référentiel technique ANME/STEG déjà cité au document 07, qui contient un chapitre dédié aux vérifications, essais et à la maintenance des CPV. Les compléments issus de bonnes pratiques internationales sont explicitement marqués comme tels.

## 2. Définitions

- **Mise en service (commissioning)** : ensemble des vérifications et essais réalisés avant la mise sous tension définitive d'une installation, visant à confirmer sa conformité et son bon fonctionnement.
- **Maintenance préventive** : ensemble d'actions planifiées et régulières visant à prévenir les défaillances (inspection visuelle, nettoyage, resserrage de connexions, etc.).
- **Maintenance corrective** : intervention réalisée après détection d'un défaut ou d'une panne.
- **Habilitation électrique** : reconnaissance formelle de la capacité d'une personne à intervenir en sécurité sur une installation électrique, notamment en présence de courant continu (DC), reconnu par le référentiel comme présentant des risques spécifiques.

## 3. Concepts principaux

### 3.1 Vérifications et essais (EXIGENCE RÉGLEMENTAIRE — référentiel ANME/STEG)

Le référentiel technique tunisien prévoit un examen de la centrale photovoltaïque avant sa mise en service, incluant notamment :

- la mesure de la résistance de la prise de terre ;
- l'essai de continuité du circuit de mise à la terre ;
- la mesure de polarité et de la tension à vide (VOC) ;
- la mesure du courant de court-circuit (ICC) et/ou de service ;
- la mesure de l'isolement du circuit courant continu ;
- l'essai fonctionnel des appareils de sectionnement, de coupure et de commande ;
- l'essai de la protection de découplage de l'onduleur.

Un rapport d'essais formalisant ces résultats est requis. Pour les installations comportant un système de stockage par batterie (BESS) et un système de conversion de puissance (PCS), des vérifications complémentaires sont prévues avant (à froid) et pendant (à chaud) la mise en service.

### 3.2 Maintenance (EXIGENCE RÉGLEMENTAIRE — référentiel ANME/STEG)

Le référentiel distingue :

- la maintenance de la centrale photovoltaïque elle-même, avec des types de maintenance et une périodicité à définir, ainsi que des actions de maintenance associées ;
- la maintenance spécifique de l'ensemble batteries et PCS, distinguant les interventions hors tension et sous tension.

Le référentiel insiste sur la **sécurité de l'intervention** sur une centrale photovoltaïque, en particulier :

- le risque spécifique lié au courant continu (DC), qui ne peut pas être coupé aussi simplement que le courant alternatif et reste présent tant que les modules sont éclairés ;
- la nécessité d'une habilitation du personnel intervenant sur l'installation ;
- des étapes de consignation formalisées avant toute intervention sur une centrale photovoltaïque.

### 3.3 Étiquetage et signalisation (EXIGENCE RÉGLEMENTAIRE — référentiel ANME/STEG)

Le référentiel prévoit l'identification et l'étiquetage systématique des équipements installés (partie AC, partie DC, onduleur, batterie), afin de faciliter les interventions de maintenance et la sécurité du personnel.

### 3.4 Défauts fréquents et bonnes pratiques internationales (RECOMMANDATION / BONNE PRATIQUE INTERNATIONALE — non issu du référentiel tunisien)

Sur la base de la pratique professionnelle internationale généralement documentée (guides d'installateurs, littérature technique du secteur PV), les défauts les plus fréquemment rencontrés incluent :

- l'encrassement des modules (poussière, sable, fientes d'oiseaux) réduisant la production ;
- les points chauds (hot spots) liés à des cellules défectueuses ou à un ombrage partiel non anticipé ;
- les défauts de connectique DC (connecteurs mal sertis, oxydation) pouvant provoquer des arcs électriques ;
- la dégradation ou la panne d'onduleur, souvent la pièce la plus sollicitée électroniquement du système ;
- la corrosion des structures porteuses en environnement côtier ou humide ;
- la baisse progressive de performance liée au vieillissement naturel des modules (dégradation annuelle typique, à vérifier auprès du fabricant pour chaque produit).

### 3.5 Risques climatiques (RECOMMANDATION / BONNE PRATIQUE INTERNATIONALE)

Les installations PV sont exposées à des risques climatiques qu'il convient d'anticiper dès la conception et de surveiller en exploitation : vent fort (voir dimensionnement structurel au document 07), grêle, fortes chaleurs affectant les composants électroniques, et accumulation de sable en environnement désertique ou semi-aride, pertinent pour le contexte tunisien bien que ce document ne fournisse pas de statistique officielle sur la fréquence de ces événements.

## 4. Méthodes / formules

Ce document ne comporte pas de formule de calcul spécifique ; il complète les formules de dimensionnement présentées au document 07 (structures, protections) et au document 05/06 (production).

## 5. Bonnes pratiques

- Planifier une inspection visuelle régulière (état des modules, câblage apparent, structure).
- Prévoir un nettoyage périodique des modules, avec une fréquence adaptée à l'environnement local (poussière, proximité de sources de pollution).
- Surveiller la production via un système de monitoring pour détecter rapidement toute baisse de performance anormale.
- Documenter chaque intervention de maintenance (date, nature, résultat) pour assurer la traçabilité.
- Respecter strictement les procédures de consignation avant toute intervention sur la partie DC de l'installation.
- Faire réaliser les opérations de maintenance et de dépannage par du personnel habilité, conformément aux exigences du référentiel technique tunisien.

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (à but illustratif uniquement) : une installation fictive constate une baisse de production de l'ordre de 8 % sur un mois par rapport aux mois précédents à irradiation comparable. Une inspection révèle un encrassement significatif des modules dû à une accumulation de poussière. Après nettoyage, la production retrouve un niveau proche de la référence historique. Cet exemple illustre l'intérêt du monitoring combiné à une maintenance préventive ; il ne représente aucune donnée réelle mesurée.

## 7. Limites et précautions

- Les exigences réglementaires précises de mise en service et de maintenance doivent être vérifiées dans la version à jour du référentiel technique ANME/STEG (document 07).
- Les éléments de bonnes pratiques internationales présentés ici (défauts fréquents, risques climatiques) sont des généralités professionnelles et non des données statistiques officielles tunisiennes.
- Toute intervention sur la partie courant continu d'une installation PV présente un risque électrique spécifique et ne doit être réalisée que par du personnel habilité.

## 8. Application à GreenDC Advisor

Ce document permet à l'assistant RAG de :

- répondre aux questions relatives à l'exploitation et à la maintenance d'une installation PV associée à un Data Center tunisien ;
- rappeler les exigences de sécurité et d'habilitation avant toute intervention ;
- alimenter les recommandations de suivi de performance dans les simulations et le monitoring proposés par GreenDC Advisor.

## 9. Sources et références

- ANME / STEG — *Référentiel technique des installations photovoltaïques raccordées au réseau électrique national basse tension* (chapitres relatifs aux vérifications, essais et à la maintenance). Voir document 07 pour l'URL de référence.
- Bonnes pratiques internationales de maintenance PV, issues de la littérature technique générale du secteur (non attribuables à une source unique officielle ; à considérer comme des recommandations générales et non comme des exigences tunisiennes).

Ce document reformule et résume des contenus publics ; les éléments non issus explicitement du référentiel tunisien sont clairement identifiés comme des recommandations générales.
