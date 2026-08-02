# Structure des routes Angular — Ramalo / RCL — V1

## 1. Principe général

L’application Angular est organisée autour de trois grandes zones de navigation :

```txt
Routes publiques
Routes connectées
Routes métier
```

Structure conceptuelle :

```txt
/
├── login
└── app
    ├── home
    ├── select-organization
    ├── select-club
    ├── member
    └── manage
```

Les routes sous `/app` nécessitent une session utilisateur valide.

## 2. Routes publiques

Les routes publiques sont accessibles sans authentification.

```txt
/login
/login/password
/login/email-code
/login/email-code/verify
```

En V1, le login classique peut être le seul mode fonctionnel, mais les routes ou sous-états doivent permettre d’ajouter rapidement le login par code email.

### 2.1 `/login`

Page d’entrée de connexion.

Objectif :

```txt
Saisir l’adresse email et choisir une méthode de connexion.
```

Cette page peut être un écran en plusieurs étapes plutôt qu’un ensemble de pages séparées.

### 2.2 `/login/password`

Connexion classique par mot de passe.

Champs :

```txt
Email
Mot de passe
```

Action :

```txt
Se connecter
```

### 2.3 `/login/email-code`

Future route pour demander un code email.

Champs :

```txt
Email
```

Action :

```txt
Recevoir un code
```

### 2.4 `/login/email-code/verify`

Future route pour vérifier le code reçu.

Champs :

```txt
Code temporaire
```

Actions :

```txt
Vérifier le code
Renvoyer un code
Utiliser mon mot de passe
```

## 3. Routes connectées

Toutes les routes connectées sont placées sous :

```txt
/app
```

Elles partagent un layout connecté :

```txt
ConnectedShellComponent
```

Ce layout contient :

```txt
Header
Navigation desktop
Drawer mobile
Zone de contenu
```

Structure :

```txt
/app
├── home
├── select-organization
├── select-club
├── member
└── manage
```

## 4. Route `/app`

La route `/app` ne devrait pas afficher directement une page métier.

Elle sert de point d’entrée après authentification.

Comportement :

```txt
/app
-> charge la session
-> charge le contexte courant
-> redirige vers la bonne destination
```

Redirections possibles :

```txt
/app/select-organization
/app/select-club
/app/home
/app/member
/app/manage
```

## 5. Sélection de contexte

### 5.1 `/app/select-organization`

Cette route est utilisée si l’utilisateur a accès à plusieurs organisations et qu’aucune organisation courante n’est encore sélectionnée.

Objectif :

```txt
Choisir l’organisation courante.
```

Après sélection :

```txt
-> charger les clubs accessibles
-> rediriger vers /app/select-club si nécessaire
-> sinon rediriger vers /app/home ou espace adapté
```

### 5.2 `/app/select-club`

Cette route est utilisée si l’utilisateur doit choisir un club courant.

Objectif :

```txt
Choisir le club courant.
```

Après sélection :

```txt
-> rediriger vers /app/home
-> ou vers le dernier espace utilisé
-> ou vers l’espace adapté aux capacités
```

### 5.3 Cas sans accès métier

Si l’utilisateur est connecté mais n’a accès à aucune organisation ou aucun club exploitable, afficher une page dédiée.

Route possible :

```txt
/app/no-access
```

Message :

```txt
Votre compte existe, mais vous n’avez encore accès à aucun club.
Contactez un responsable de votre organisation.
```

## 6. Route `/app/home`

La route `/app/home` est la page d’orientation après résolution du contexte.

Elle est surtout utile si l’utilisateur a plusieurs casquettes.

Cas possibles :

```txt
Membre uniquement
-> redirection vers /app/member

Gestion uniquement
-> redirection vers /app/manage

Membre + gestion
-> afficher le choix entre les deux espaces
```

Exemple d’écran :

```txt
Que voulez-vous faire ?

[ Accéder à mon espace membre ]
[ Accéder à l’espace gestion ]
```

## 7. Routes espace membre

Toutes les routes de l’espace membre sont sous :

```txt
/app/member
```

Structure :

```txt
/app/member
├── events
│   └── :eventId
├── registrations
└── profile
```

### 7.1 `/app/member`

Dashboard membre.

Objectif :

```txt
Afficher les prochains événements, les prochaines inscriptions et les actions rapides.
```

### 7.2 `/app/member/events`

Liste des événements accessibles au membre.

Fonctions :

```txt
Voir les événements à venir
Voir le statut d’inscription personnel
S’inscrire si possible
Ouvrir le détail
```

### 7.3 `/app/member/events/:eventId`

Détail événement côté membre.

Fonctions :

```txt
Consulter les informations de l’événement
Voir mon statut d’inscription
S’inscrire
Annuler mon inscription si permis
```

### 7.4 `/app/member/registrations`

Liste des inscriptions de l’utilisateur.

Fonctions :

```txt
Voir mes inscriptions à venir
Voir mes inscriptions passées
Voir mes inscriptions annulées
Ouvrir l’événement lié
Annuler si permis
```

### 7.5 `/app/member/profile`

Profil du membre connecté.

Fonctions V1 :

```txt
Voir mes informations personnelles
Voir mes clubs
Voir mon adhésion
Voir mes responsabilités éventuelles
```

## 8. Routes espace gestion

Toutes les routes de l’espace gestion sont sous :

```txt
/app/manage
```

Structure :

```txt
/app/manage
├── members
│   ├── new
│   ├── :personId
│   └── :personId/edit
└── events
    ├── new
    ├── :eventId
    ├── :eventId/edit
    ├── :eventId/registrations
    └── :eventId/public-registration-requests
```

### 8.1 `/app/manage`

Dashboard gestion.

Objectif :

```txt
Afficher les actions importantes côté gestion :
- membres à suivre
- événements à venir
- demandes publiques en attente
- actions rapides
```

### 8.2 `/app/manage/members`

Liste des membres.

Fonctions :

```txt
Rechercher un membre
Filtrer les membres
Créer un membre
Ouvrir le détail membre
Modifier un membre
```

### 8.3 `/app/manage/members/new`

Création d’un membre.

Champs minimum V1 :

```txt
Prénom
Nom
Email
```

### 8.4 `/app/manage/members/:personId`

Détail membre.

Sections possibles :

```txt
Identité
Adhésion
Responsabilités
Inscriptions aux événements
Historique / notes
```

### 8.5 `/app/manage/members/:personId/edit`

Modification des informations principales d’un membre.

Champs V1 :

```txt
Prénom
Nom
Email
```

### 8.6 `/app/manage/events`

Liste des événements côté gestion.

Fonctions :

```txt
Rechercher un événement
Filtrer par statut
Créer un événement
Ouvrir le détail événement
Accéder aux inscriptions
Accéder aux demandes publiques
```

### 8.7 `/app/manage/events/new`

Création d’un événement.

Sections du formulaire :

```txt
Informations générales
Date et heure
Inscriptions
Publication
```

### 8.8 `/app/manage/events/:eventId`

Détail événement côté gestion.

Sections ou onglets :

```txt
Informations
Inscriptions
Demandes publiques
```

Cette route est la page centrale de gestion d’un événement.

### 8.9 `/app/manage/events/:eventId/edit`

Modification d’un événement.

### 8.10 `/app/manage/events/:eventId/registrations`

Liste des inscriptions à un événement.

Cette route peut être une vraie page ou un onglet profond du détail événement.

Fonctions :

```txt
Voir les inscrits
Filtrer par statut
Rechercher par nom/email
Annuler une inscription si permis
Voir la personne liée
```

### 8.11 `/app/manage/events/:eventId/public-registration-requests`

Liste des demandes publiques d’un événement.

Fonctions :

```txt
Voir les demandes
Filtrer par statut
Rechercher par nom/email
Accepter une demande
Refuser une demande
Voir le détail d’une demande
```

## 9. Routes futures possibles

Certaines routes sont volontairement hors V1, mais doivent rester possibles.

### 9.1 Administration organisation

```txt
/app/admin
/app/admin/clubs
/app/admin/users
/app/admin/settings
```

### 9.2 Administration système

```txt
/app/system
/app/system/organizations
/app/system/plans
```

### 9.3 Demandes publiques globales

```txt
/app/manage/public-registration-requests
```

Cette route pourrait afficher toutes les demandes publiques en attente pour le club courant.

### 9.4 Cotisations et renouvellements

```txt
/app/manage/fees
/app/manage/members/:personId/memberships
```

Ces routes ne sont pas nécessaires en V1, car les adhésions ne sont pas limitées dans le temps et les cotisations sont gérées dans une application financière séparée.

## 10. Guards Angular

Les routes doivent être protégées par des guards simples et composables.

### 10.1 `authGuard`

Protège les routes connectées.

Utilisé sur :

```txt
/app/**
```

Rôle :

```txt
Vérifier qu’une session utilisateur existe.
Rediriger vers /login si nécessaire.
```

### 10.2 `contextGuard`

Protège les routes métier qui nécessitent un contexte courant.

Rôle :

```txt
Vérifier que l’organisation et le club courant sont résolus.
Rediriger vers la sélection de contexte si nécessaire.
```

### 10.3 `capabilityGuard`

Protège les routes selon les capacités.

Exemples :

```txt
/app/member/**
-> canAccessMemberArea

/app/manage/**
-> canAccessManagementArea

/app/manage/members/**
-> canManageMembers

/app/manage/events/**
-> canManageEvents ou capacité liée à l’événement
```

### 10.4 `guestGuard`

Protège les routes de login.

Rôle :

```txt
Si l’utilisateur est déjà connecté, rediriger vers /app.
```

## 11. Résolution des capacités

Le frontend doit éviter les conditions de rôle directes.

À éviter :

```txt
role === 'club_manager'
```

À préférer :

```txt
canManageMembers
canManageEvents
canReviewPublicRegistrationRequests
canManageEventRegistrations
```

Les routes doivent idéalement déclarer les capacités nécessaires dans leurs métadonnées.

Exemple conceptuel :

```txt
/app/manage/members
requires: canManageMembers
```

```txt
/app/manage/events/:eventId/public-registration-requests
requires: canReviewPublicRegistrationRequests
```

## 12. Redirections principales

### 12.1 Utilisateur non connecté

```txt
/app/member
-> /login
```

### 12.2 Utilisateur connecté sans contexte

```txt
/app/member
-> /app/select-organization
```

ou :

```txt
/app/select-club
```

### 12.3 Utilisateur sans permission membre

```txt
/app/member
-> /app/home
```

ou page accès interdit.

### 12.4 Utilisateur sans permission gestion

```txt
/app/manage
-> /app/home
```

ou page accès interdit.

### 12.5 Ressource inaccessible

Si l’API retourne 404 pour une ressource hors organisation ou hors contexte :

```txt
Afficher une page “ressource introuvable ou non accessible”.
```

Ne pas exposer de détail technique.

## 13. Organisation Angular des routes

Structure possible :

```txt
app.routes.ts
features/auth/auth.routes.ts
features/shell/shell.routes.ts
features/member/member.routes.ts
features/management/management.routes.ts
features/members/members.routes.ts
features/events/events.routes.ts
```

### 13.1 `app.routes.ts`

Contient seulement les grandes entrées :

```txt
/login
/app
redirect
not-found
```

### 13.2 `features/auth/auth.routes.ts`

Contient les routes de connexion.

### 13.3 `features/shell/shell.routes.ts`

Contient les routes sous `/app` et le layout connecté.

### 13.4 `features/member/member.routes.ts`

Contient les routes de l’espace membre.

### 13.5 `features/management/management.routes.ts`

Contient le dashboard gestion et orchestre les sous-routes gestion.

### 13.6 `features/members/members.routes.ts`

Contient les routes de gestion des membres.

### 13.7 `features/events/events.routes.ts`

Contient les routes de gestion des événements.

## 14. Ordre d’implémentation recommandé

### Étape 1 — Routes et shell minimal

```txt
/app
/login
ConnectedShell
Header
Navigation desktop/mobile minimale
```

### Étape 2 — Auth classique

```txt
Login email/mot de passe
Session
Refresh token
Logout
Redirection après login
```

### Étape 3 — Contexte courant

```txt
/me
/me/organizations
/me/current-context
Sélection organisation
Sélection club
Header contextuel
```

### Étape 4 — Capacités

```txt
CapabilityService
Navigation conditionnelle
CapabilityGuard
```

### Étape 5 — Espace gestion membres

```txt
Liste membres
Détail membre
Création membre
Modification membre
```

### Étape 6 — Espace gestion événements

```txt
Liste événements
Détail événement
Création/modification événement
```

### Étape 7 — Inscriptions et demandes publiques

```txt
Inscriptions événement
Demandes publiques
Acceptation/refus
```

### Étape 8 — Espace membre

```txt
Dashboard membre
Événements membre
Mes inscriptions
Profil
```

### Étape 9 — Login par code email

```txt
Demande de code
Vérification du code
Gestion expiration / erreurs / renvoi
```

Cette étape pourrait être avancée si l’API passwordless est développée rapidement.

## 15. Décisions actées pour les routes

Les décisions suivantes sont retenues pour la V1 :

* les routes connectées sont sous `/app` ;
* `/app` sert de point d’entrée et de redirection ;
* l’espace membre est sous `/app/member` ;
* l’espace gestion est sous `/app/manage` ;
* la gestion des membres est sous `/app/manage/members` ;
* la gestion des événements est sous `/app/manage/events` ;
* les inscriptions et demandes publiques sont rattachées aux événements ;
* les routes de login doivent permettre le futur login par code email ;
* les routes sont protégées par auth, contexte et capacités ;
* les capacités sont préférées aux rôles codés en dur ;
* les routes exactes pourront être ajustées pendant l’implémentation Angular.
