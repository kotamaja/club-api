# Frontend — Fonctionnalités attendues pour une page de liste

Ce document décrit les fonctionnalités attendues pour les pages de liste de gestion dans l’application Angular.

L’objectif n’est pas de tout implémenter immédiatement sur chaque liste, mais de garder une référence commune pour ne pas oublier les comportements importants.

## Objectif général

Une page de liste doit permettre à l’utilisateur de consulter, filtrer, trier et parcourir efficacement une collection métier.

Exemples :

* événements ;
* membres ;
* inscriptions ;
* clubs ;
* demandes d’inscription publiques.

## Chargement des données

Pour les lectures de liste, utiliser `httpResource`.

La page doit exposer un état explicite sous forme de union type :

```txt
loading
reloading
empty
loaded
error
```

La réponse API doit être validée au runtime avec Zod.

Lorsque le format backend ne correspond pas directement au modèle partagé frontend, le schema Zod peut transformer la réponse.

Exemple :

```txt
API:
{
  items: [],
  pagination: {
    page: 1,
    itemsPerPage: 20,
    totalItems: 42
  }
}

Frontend:
{
  items: [],
  page: 1,
  perPage: 20,
  totalItems: 42
}
```

## Pagination

Une liste de gestion doit utiliser la pagination backend.

Fonctionnalités minimales :

* page courante ;
* nombre d’éléments par page ;
* nombre total d’éléments ;
* bouton précédent ;
* bouton suivant ;
* résumé visible, par exemple : `Page 1 sur 3 — 42 éléments`.

Les changements de filtre, recherche ou tri doivent revenir à la page 1.

## Tri

Les colonnes importantes doivent pouvoir être triées si le backend le supporte.

Format API Platform recommandé :

```txt
order[startsAt]=asc
order[title]=desc
```

Le frontend garde une représentation UI simple :

```txt
sort: startsAt
direction: asc
```

La conversion vers le format API est faite dans la resource ou dans un mapper dédié.

## Filtres

Une liste peut contenir des filtres métier simples.

Exemples pour les événements :

```txt
period: upcoming | past | all
type: all | general | course | training | trip | meeting | competition
status: all | draft | published | cancelled | archived
```

Certains filtres sont des filtres UI et ne sont pas envoyés directement à l’API.

Exemple :

```txt
period=upcoming
```

devient côté API :

```txt
startsAt[after]=<now>
```

## Recherche texte

La recherche texte doit utiliser un champ UI unique :

```txt
search
```

Le backend décide ensuite sur quels champs la recherche est appliquée.

Exemple pour les événements :

```txt
search=delémont
```

peut chercher dans :

```txt
title
location
```

La recherche texte doit être debouncée, par exemple avec un délai de 300 ms.

Les filtres par select, le tri et la pagination ne nécessitent pas de debounce.

## Synchronisation avec l’URL

Les filtres, le tri, la pagination et la recherche doivent être synchronisés avec l’URL.

Objectifs :

* conserver les filtres après F5 ;
* permettre le partage d’une URL filtrée ;
* revenir correctement depuis une page détail ;
* éviter de perdre le contexte de consultation.

Exemple :

```txt
/app/manage/events?search=jura&type=course&status=draft&sort=startsAt&direction=asc&page=2
```

Les valeurs par défaut doivent être omises pour garder une URL lisible.

Exemple préféré :

```txt
/app/manage/events
```

plutôt que :

```txt
/app/manage/events?period=upcoming&status=all&type=all&page=1&perPage=20&sort=startsAt&direction=asc
```

## Ligne sélectionnée / dernier élément consulté

Une liste peut mémoriser le dernier élément consulté avec un paramètre URL séparé :

```txt
selected=<id>
```

Ce paramètre est un état UI. Il ne doit pas faire partie de la query API.

Objectif :

```txt
liste
-> ouverture d’un détail
-> retour à la liste
-> l’élément précédemment consulté est mis en surbrillance
```

Pour une interface mobile-friendly, le comportement recommandé est :

```txt
clic / tap sur la ligne entière
-> ouvre le détail
```

La ligne doit visuellement indiquer qu’elle est cliquable.

## Labels métier

Les valeurs techniques ne doivent pas être affichées directement à l’utilisateur.

Exemples :

```txt
draft -> Brouillon
published -> Publié
course -> Cours
training -> Entraînement
```

Pour la V1, ces labels peuvent être codés en dur côté frontend.

À terme, les labels des enums métier devraient venir de l’API.

## Enums métier — amélioration future

À faire plus tard : exposer les enums métier via des endpoints API read-only.

Exemples :

```txt
/api/v1/referentials/event-types
/api/v1/referentials/event-statuses
```

Format minimal envisagé :

```json
[
  {
    "value": "course",
    "label": "Cours"
  },
  {
    "value": "training",
    "label": "Entraînement"
  }
]
```

Objectifs :

* éviter de dupliquer les valeurs backend dans le frontend ;
* centraliser les labels ;
* faciliter les traductions futures ;
* contrôler l’ordre d’affichage ;
* préparer l’ajout de nouvelles valeurs sans devoir modifier plusieurs écrans.

Pour la V1, cette amélioration peut attendre. Les enums peuvent rester codés en dur côté Angular tant que les valeurs sont stables.

## Reset des filtres

Une liste avec plusieurs filtres doit proposer une action :

```txt
Réinitialiser
```

Cette action remet :

* recherche vide ;
* filtres à leur valeur par défaut ;
* tri par défaut ;
* page à 1 ;
* URL nettoyée.

## Colonnes métier utiles

Chaque liste doit afficher les colonnes nécessaires à la prise de décision rapide.

Exemple pour les événements :

```txt
Titre
Type
Date
Lieu
Statut
Inscriptions / capacité
Club
```

Pour les événements, la colonne inscriptions peut afficher :

```txt
1 / 10
0 / illimité
```

## À ne pas implémenter systématiquement en V1

Les fonctionnalités suivantes sont utiles mais ne doivent pas être ajoutées par défaut :

* sélection multiple ;
* actions groupées ;
* colonnes configurables ;
* export CSV ;
* sauvegarde de vues ;
* recherche par date précise ;
* filtres avancés complexes ;
* tri sur toutes les colonnes ;
* préférences utilisateur persistées.

Elles pourront être ajoutées si un besoin métier clair apparaît.

## Checklist rapide

Pour chaque nouvelle page de liste, vérifier :

```txt
[ ] httpResource pour la lecture
[ ] schema Zod
[ ] état loading / reloading / empty / error / loaded
[ ] pagination backend
[ ] tri utile
[ ] filtres métier utiles
[ ] recherche texte si pertinente
[ ] debounce sur recherche texte
[ ] synchronisation URL
[ ] reset filtres
[ ] labels lisibles
[ ] selected row si navigation détail
[ ] résumé du nombre de résultats
[ ] colonnes métier suffisantes
[ ] mapping frontend -> API isolé
```
