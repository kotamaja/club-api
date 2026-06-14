# Architecture Community / Pro

## Objectif

Le projet `club-api` / Ramalo est une application Symfony / API Platform multi-tenant destinée à gérer des organisations, des clubs, des personnes, des adhésions, des groupes de membres, puis progressivement des événements.

À terme, le projet pourrait être structuré selon une approche de type **open core** :

* une édition **Community**, probablement open source, contenant le socle fonctionnel de base ;
* une édition **Pro**, propriétaire ou commerciale, contenant les fonctionnalités avancées ;
* éventuellement plusieurs niveaux de service supplémentaires : Community, Standard, Pro, Enterprise, etc.

L’objectif de cette note est de définir une première organisation technique permettant de préparer cette évolution sans sur-architecturer prématurément le projet.

La priorité actuelle est de garder un code simple, maintenable et testable, tout en évitant de mélanger définitivement le code commun, le code Community et les futures extensions Pro.

---

## Décision actuelle

Pour l’instant, le projet reste dans un **dépôt Git privé unique** :

```txt
club-api
```

Il n’y a pas encore de séparation physique entre plusieurs dépôts, packages Composer ou bundles Symfony.

La séparation est uniquement **logique et interne** au code source.

Structure cible :

```txt
src/
  Core/
  Community/
  Pro/

tests/
  Core/
  Community/
  Pro/
```

Cette organisation permet de commencer à discipliner les frontières internes, tout en gardant une expérience de développement simple.

---

## Rôle des différentes zones

### Core

`Core` contient les éléments fondamentaux communs à toutes les éditions.

Exemples :

```txt
src/Core/
  Entity/
  Enum/
  Feature/
  Limit/
  Contract/
  Exception/
  Security/
```

Le `Core` peut contenir notamment :

* les entités fondamentales ;
* les contrats de services ;
* les interfaces de policies ;
* les enums communes ;
* les exceptions communes ;
* les services transverses ;
* les concepts liés au multi-tenant ;
* les éléments de sécurité communs.

Exemples d’éléments appartenant au `Core` :

```txt
Organization
Club
Person
Membership
ConnectionUser
OrganizationUser

ServicePlan
Feature
Limit

FeatureCheckerInterface
LimitCheckerInterface

FeatureNotAvailableException
LimitExceededException
```

Pour la future brique événementielle, les contrats communs pourront également être placés dans le `Core` :

```txt
EventVisibilityPolicyInterface
EventRegistrationEligibilityPolicyInterface
EventCapacityPolicyInterface
EventSelectionPolicyInterface
EventRegistrationProcessorInterface
```

Le `Core` ne doit contenir aucune dépendance vers `Community` ou `Pro`.

---

### Community

`Community` contient les implémentations de base du produit.

Il s’agit de l’édition fonctionnelle minimale, correspondant au socle qui pourrait être ouvert plus tard en open source.

Exemples :

```txt
src/Community/
  Event/
    Policy/
    Processor/
    Provider/
  Capability/
```

Le code Community peut contenir :

* les implémentations simples des policies ;
* les processors de base ;
* les providers de base ;
* le catalogue de features Community ;
* le catalogue de limits Community ;
* les comportements métier disponibles dans l’édition de base.

Exemples conceptuels :

```txt
CommunityFeatureCatalog
CommunityLimitCatalog
CommunityEventCapacityPolicy
CommunityEventVisibilityPolicy
CommunityEventRegistrationProcessor
```

`Community` peut dépendre du `Core`.

`Community` ne doit jamais dépendre de `Pro`.

---

### Pro

`Pro` contient les extensions avancées, propriétaires ou commerciales.

Exemples :

```txt
src/Pro/
  Event/
    Policy/
    Processor/
    Provider/
  Capability/
```

Le code Pro peut contenir :

* des policies avancées ;
* des processors avancés ;
* des providers spécifiques ;
* des décorateurs de services Community ;
* des remplacements de services Community ;
* des features avancées ;
* des limits différentes ou plus élevées.

Exemples conceptuels :

```txt
ProFeatureCatalog
ProLimitCatalog
ProEventCapacityPolicy
ProEventVisibilityPolicy
ProEventRegistrationProcessor
```

`Pro` peut dépendre du `Core`.

`Pro` peut remplacer ou décorer certains services Community lorsque c’est utile.

---

## Règles de dépendance

Les règles de dépendance sont les suivantes :

```txt
Core ne dépend ni de Community ni de Pro.
Community dépend de Core.
Pro dépend de Core.
Pro peut éventuellement décorer ou remplacer Community.
Community ne dépend jamais de Pro.
```

Schéma simplifié :

```txt
Core
  ↑
  |
Community

Core
  ↑
  |
Pro
```

Et éventuellement :

```txt
Community
  ↑
  |
Pro
```

uniquement dans le cas où un service Pro décore ou étend explicitement un comportement Community.

Les dépendances inverses sont interdites :

```txt
Core -> Community
Core -> Pro
Community -> Pro
```

Cette règle est essentielle pour permettre une extraction future propre.

---

## Plans, features et limits

Le code métier ne doit pas dépendre directement du nom commercial du plan.

À éviter :

```php
if ($organization->getPlan() === ServicePlan::Pro) {
    // autorisé
}
```

À privilégier :

```php
if (!$featureChecker->isEnabled($organization, Feature::EventCustomForm)) {
    throw new FeatureNotAvailableException(Feature::EventCustomForm);
}
```

Règle générale :

```txt
Les plans sont commerciaux.
Les features sont métier/techniques.
Les limits sont quantitatives.
Le code métier dépend des features et limits, pas du nom du plan.
```

Concepts prévus :

```txt
ServicePlan
Feature
Limit
FeatureCheckerInterface
LimitCheckerInterface
```

Exemples de features possibles pour la future brique Event :

```txt
event.basic
event.waitlist
event.multi_session
event.custom_form
event.manual_selection
event.group_visibility
event.group_eligibility
event.interclub
event.attendance_tracking
event.documents
```

Exemples de limits possibles :

```txt
max_clubs
max_members
max_active_events
max_event_participants
```

---

## Application aux événements

La future brique `Event` servira de cas d’étude principal pour appliquer cette architecture.

La frontière produit envisagée est flexible, mais pourrait ressembler à ceci.

### Community

Fonctionnalités de base :

```txt
- événements simples
- date ou période simple
- inscription simple
- capacité globale
- liste des inscrits
- éventuellement liste d’attente simple
```

### Pro

Fonctionnalités avancées :

```txt
- événements multi-sessions
- formulaires configurables
- sélection manuelle
- visibilité par groupe
- éligibilité par groupe
- quotas par groupe
- événements interclubs
- documents
- suivi de présence
- groupes dynamiques
```

Cette frontière n’est pas figée. Elle doit pouvoir évoluer sans changer profondément le code métier.

---

## Services et policies

Les comportements variables doivent être représentés par des services, des interfaces et des policies plutôt que par des conditions dispersées dans le code.

Exemples :

```txt
EventVisibilityPolicyInterface
EventRegistrationEligibilityPolicyInterface
EventCapacityPolicyInterface
EventSelectionPolicyInterface
EventRegistrationProcessorInterface
```

L’édition Community pourra fournir des implémentations simples.

L’édition Pro pourra fournir des implémentations plus avancées, ou décorer les implémentations Community.

Exemple conceptuel :

```txt
CommunityEventCapacityPolicy
  - vérifie uniquement la capacité globale

ProEventCapacityPolicy
  - vérifie la capacité globale
  - vérifie les quotas par groupe
  - vérifie les règles interclubs
```

Le code applicatif doit dépendre de l’interface :

```php
public function __construct(
    private readonly EventCapacityPolicyInterface $capacityPolicy,
) {
}
```

et non d’une implémentation concrète Community ou Pro.

---

## Configuration Symfony

Les services doivent être configurés de manière à pouvoir remplacer ou décorer les comportements selon l’édition active.

Exemple conceptuel en édition Community :

```yaml
App\Core\Event\Policy\EventCapacityPolicyInterface:
    alias: App\Community\Event\Policy\CommunityEventCapacityPolicy
```

Exemple conceptuel en édition Pro :

```yaml
App\Core\Event\Policy\EventCapacityPolicyInterface:
    alias: App\Pro\Event\Policy\ProEventCapacityPolicy
```

Ou avec décoration :

```php
final readonly class ProEventCapacityPolicy implements EventCapacityPolicyInterface
{
    public function __construct(
        private EventCapacityPolicyInterface $inner,
    ) {
    }

    public function assertCanRegister(Event $event): void
    {
        $this->inner->assertCanRegister($event);

        // règles Pro supplémentaires
    }
}
```

La stratégie précise de configuration sera définie plus tard, lors de l’implémentation.

---

## Tests

Les tests suivent la même séparation logique que le code source :

```txt
tests/
  Core/
  Community/
  Pro/
```

Les tests du `Core` vérifient les contrats, les règles communes et les services transverses.

Les tests `Community` vérifient les comportements disponibles dans l’édition de base.

Les tests `Pro` vérifient les comportements avancés.

Les tests doivent viser les features et limits plutôt que les noms commerciaux des plans lorsque c’est possible.

À privilégier :

```php
public function testCannotCreateCustomRegistrationFormWhenFeatureIsDisabled(): void
```

À éviter :

```php
public function testCommunityCannotCreateCustomRegistrationForm(): void
```

Cette approche permet de faire évoluer les plans commerciaux sans réécrire inutilement les tests métier.

---

## Ce que l’on ne fait pas encore

À ce stade, on ne met pas encore en place :

```txt
- plusieurs dépôts Git ;
- un dépôt Community public ;
- un dépôt Pro privé ;
- un bundle Symfony Pro séparé ;
- un package Composer privé ;
- une CI/CD multi-édition complexe ;
- une table complète de plans/features/limits administrable ;
- un système de plugins dynamique ;
- une vraie logique de billing ou subscription.
```

Ces éléments pourront être introduits plus tard si le besoin devient réel.

Pour l’instant, l’objectif est de garder une séparation logique propre dans un dépôt unique.

---

## Trajectoire future possible

Si le projet évolue vers une vraie distribution open core, la trajectoire envisagée est la suivante :

```txt
Aujourd’hui :
  dépôt privé unique club-api
  séparation logique Core / Community / Pro

Plus tard :
  extraction possible de Community vers un dépôt public
  extraction possible de Pro vers un dépôt privé

Encore plus tard :
  Pro sous forme de bundle Symfony ou package Composer privé
```

Exemple possible :

```txt
ramalo/community
ramalo/pro-bundle
```

ou :

```txt
ramalo/club-api
ramalo/club-api-pro
```

La forme exacte n’est pas décidée aujourd’hui.

La décision actuelle vise surtout à ne pas bloquer cette évolution.

---

## Résumé de la décision

Décision actuelle :

```txt
Un seul dépôt Git privé.
Une séparation logique interne Core / Community / Pro.
Pas encore de séparation physique.
Pas encore de bundle Pro.
Le métier dépend de Feature, Limit et policies, pas directement de Community ou Pro.
Les frontières internes doivent être respectées dès maintenant.
```

Règle essentielle :

```txt
Core ne connaît ni Community ni Pro.
Community ne connaît jamais Pro.
Pro peut connaître Core, et éventuellement décorer/remplacer Community.
```

Cette approche doit permettre de garder le développement simple aujourd’hui, tout en préparant une extraction plus propre demain si Ramalo évolue vers une vraie architecture open core.
