# Servers and IT Equipment Energy Efficiency

## 1. Introduction

Les équipements informatiques (serveurs, stockage, réseau) constituent la charge utile d'un Data Center, mais leur consommation réelle dépend fortement de leur taux d'utilisation et de leur gestion. Ce document présente les concepts clés relatifs à l'efficacité énergétique de ces équipements, en s'appuyant sur les publications ASHRAE (Datacom Series) et le DOE.

## 2. Définitions

- **Puissance nominale (nameplate power)** : puissance maximale indiquée par le fabricant sur la plaque signalétique de l'équipement, correspondant généralement à un fonctionnement à pleine charge, rarement atteint en pratique.
- **Consommation réelle** : puissance effectivement consommée par l'équipement en fonctionnement, dépendante de la charge de travail instantanée.
- **Taux d'utilisation (utilization rate)** : proportion de la capacité de calcul ou de stockage effectivement sollicitée par les applications.
- **Virtualisation** : technique permettant de faire fonctionner plusieurs machines virtuelles sur un même serveur physique, augmentant le taux d'utilisation.
- **Consolidation** : regroupement de plusieurs charges de travail sur un nombre réduit de serveurs physiques.
- **Power management** : ensemble des mécanismes matériels et logiciels (mise en veille, ajustement dynamique de fréquence/tension, extinction automatique) permettant de réduire la consommation lors des périodes de faible charge.

## 3. Concepts principaux

### 3.1 Puissance nominale vs consommation réelle

La puissance nominale surestime généralement la consommation réelle, car elle correspond à un scénario de charge maximale rarement rencontré en exploitation continue. La consommation réelle dépend de la charge de travail, du niveau de virtualisation, et des mécanismes de gestion d'énergie activés.

### 3.2 Serveurs sous-utilisés

Un serveur physique fonctionnant à faible taux d'utilisation (par exemple un serveur exécutant une seule application peu sollicitée) consomme souvent une part importante de sa puissance de base (idle power) indépendamment de la charge, ce qui dégrade son efficacité énergétique rapportée au service rendu. L'identification des serveurs sous-utilisés ou obsolètes ("serveurs fantômes" ou "zombies", qui ne rendent plus de service actif) est une pratique recommandée d'audit énergétique.

### 3.3 Virtualisation et consolidation

La virtualisation permet de regrouper plusieurs charges de travail sur un nombre réduit de serveurs physiques, augmentant le taux d'utilisation moyen et réduisant le nombre total d'équipements à alimenter et refroidir.

### 3.4 Stockage, réseau et équipements associés

- **Stockage** : la consommation dépend du nombre de disques actifs, de leur technologie (HDD vs SSD) et du taux d'utilisation de la capacité.
- **Switches et routeurs** : leur consommation dépend du nombre de ports actifs, du débit et des fonctionnalités activées.
- **Firewalls** et autres équipements de sécurité réseau : consomment généralement une puissance stable indépendamment de la charge de trafic.
- **UPS (Uninterruptible Power Supply)** : leur rendement varie fortement selon le taux de charge ; un UPS très sous-dimensionné par rapport à sa charge réelle fonctionne souvent avec un rendement dégradé.

### 3.5 Power management

Les mécanismes de gestion dynamique de l'énergie (ajustement de la fréquence du processeur selon la charge, mise en veille des composants inactifs) permettent de réduire la consommation pendant les périodes creuses, sans dégrader la disponibilité du service si correctement configurés.

## 4. Méthodes / formules

Il n'existe pas de formule unique ; l'évaluation repose généralement sur :

```
Efficacité IT = Service rendu (calcul, transactions, capacité utile) / Énergie consommée par les équipements IT
```

Cette efficacité peut être approchée par des indicateurs sectoriels spécifiques (par exemple des indicateurs de performance par serveur), qui ne sont pas standardisés de façon universelle et ne sont pas détaillés ici pour éviter toute présentation de valeur non vérifiée comme une norme.

## 5. Bonnes pratiques

- Réaliser un inventaire régulier des serveurs et identifier les équipements sous-utilisés ou obsolètes.
- Prioriser la virtualisation et la consolidation des charges de travail avant tout investissement dans de nouveaux serveurs.
- Activer les mécanismes de gestion d'énergie disponibles sur les équipements, en évaluant leur impact sur la performance applicative.
- Lors du remplacement d'équipements, privilégier des matériels à haut rendement énergétique (alimentations certifiées à haut rendement, disques SSD pour les charges nécessitant des accès fréquents).
- Dimensionner les UPS de façon à fonctionner dans une plage de charge où leur rendement est favorable, plutôt qu'en surdimensionnement systématique.

## 6. Exemple pédagogique

**EXEMPLE PÉDAGOGIQUE** (valeurs fictives, à but illustratif uniquement) :

Un serveur physique fonctionnant seul à 10 % de sa capacité consomme, dans cet exemple fictif, 150 W. Après consolidation de trois charges de travail similaires sur ce même serveur (taux d'utilisation porté à 30 %), la consommation passe à 180 W pour un service rendu trois fois supérieur, ce qui améliore fortement l'efficacité rapportée au service rendu, sans qu'aucune valeur générique de gain ne soit ici affirmée comme représentative de cas réels.

## 7. Limites et précautions

- Les gains liés à la virtualisation et à la consolidation dépendent fortement du type de charge applicative (certaines applications ne se virtualisent pas facilement ou nécessitent des ressources dédiées).
- La réduction du nombre de serveurs physiques doit être compatible avec les exigences de disponibilité, de résilience et de séparation des environnements (production, test).
- Le remplacement d'équipements a un coût environnemental (fabrication, transport) qui doit être mis en balance avec le gain énergétique attendu.

## 8. Application à GreenDC Advisor

Ce document permet à l'assistant RAG de :

- expliquer pourquoi la puissance nominale d'un équipement ne doit pas être utilisée seule pour estimer la consommation réelle ;
- appuyer les recommandations de consolidation/virtualisation dans l'arbre de décision du document 12 (cas "serveur sous-utilisé") ;
- fournir un contexte qualitatif pour affiner l'estimation de consommation utilisée dans le dimensionnement photovoltaïque (document 06).

## 9. Sources et références

- ASHRAE — *Datacom Series* (référence sur les équipements et pratiques des Data Centers).
- U.S. Department of Energy (DOE) / FEMP — *Best Practices Guide for Energy-Efficient Data Center Design*.

Ce document est une synthèse pédagogique reformulée à partir de sources publiques ; aucune valeur chiffrée présentée ici (hors exemple explicitement marqué) ne doit être interprétée comme une donnée officielle.
