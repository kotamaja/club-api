# Cahier des charges GUI / UX / Navigation — Ramalo / RCL — V1

## 1. Objectif général UX

L’application Ramalo V1 doit proposer une interface web claire, responsive et utilisable aussi bien sur ordinateur que sur mobile.

Elle doit permettre à un utilisateur connecté de comprendre rapidement :

* dans quel club il se trouve ;
* s’il agit comme membre ou comme gestionnaire ;
* quelles actions sont disponibles ;
* quels éléments nécessitent son attention ;
* comment revenir à ses informations importantes.

L’UX doit rester simple, même si le modèle métier sous-jacent est multi-tenant, multi-club et basé sur des responsabilités cumulables.

## 2. Surfaces principales

L’application connectée est organisée autour de deux grands espaces :

```txt id="zegrcz"
Espace membre
Espace gestion
```

Ces deux espaces appartiennent à la même application Angular principale.

Un utilisateur peut avoir accès à un seul espace ou aux deux, selon ses droits.

## 3. Espace membre

L’espace membre est destiné à l’utilisateur qui agit en tant que membre d’un club.

Il permet de :

* consulter les événements accessibles ;
* voir ses inscriptions ;
* s’inscrire à un événement ;
* annuler une inscription si les règles le permettent ;
* consulter son profil ;
* consulter son appartenance au club.

Navigation proposée :

```txt id="k3bsw6"
Accueil
Événements
Mes inscriptions
Mon profil
```

L’espace membre doit être simple, rassurant et non administratif.

## 4. Espace gestion

L’espace gestion est destiné aux utilisateurs ayant des responsabilités opérationnelles ou administratives sur un club.

Il repose sur deux piliers prioritaires :

```txt id="qumlbs"
1. Membres
2. Événements
```

La gestion des membres est prioritaire en V1, au même titre que la gestion des événements, des inscriptions et des demandes publiques.

Navigation proposée :

```txt id="bkiog6"
Tableau de bord
Membres
Événements
```

Les demandes publiques sont principalement rattachées aux événements en V1, mais une page globale pourra être ajoutée plus tard.

Structure générale :

```txt id="a2qs6k"
Espace gestion
├── Tableau de bord
├── Membres
│   ├── Liste des membres
│   ├── Détail membre
│   └── Création / modification membre
└── Événements
    └── Détail événement
        ├── Informations
        ├── Inscriptions
        └── Demandes publiques
```

## 5. Profils et responsabilités

Les notions suivantes ne sont pas exclusives :

* ClubMember ;
* ClubOperator ;
* ClubManager.

Une personne peut être :

* membre uniquement ;
* membre et opérateur ;
* membre et gestionnaire ;
* gestionnaire sans forcément utiliser l’espace membre ;
* plus tard, administrateur d’organisation.

La distinction UX importante est :

```txt id="hf9e78"
ClubMember = appartenance au club
ClubOperator = responsabilités opérationnelles limitées
ClubManager = responsabilités de gestion larges
```

L’interface doit permettre à l’utilisateur de comprendre dans quelle “casquette” il agit.

## 6. Permissions et capacités

L’interface ne doit pas dépendre uniquement de rôles codés en dur.

Elle doit être pensée autour de capacités.

Exemples :

```txt id="pfnp5x"
canAccessMemberArea
canAccessManagementArea
canManageMembers
canManageMemberships
canManageEvents
canManageEventRegistrations
canReviewPublicRegistrationRequests
canManageClubSettings
```

Les menus, boutons, routes et actions doivent être affichés selon les capacités disponibles dans le contexte courant.

Le frontend ne remplace toutefois jamais la sécurité backend. Si l’API refuse une action, l’interface doit afficher un message clair.

## 7. Header connecté

Le header est présent sur toutes les pages connectées.

Il doit afficher le contexte utile sans surcharger l’interface.

Règles actées :

```txt id="kdzcvk"
Le club courant est toujours affiché.

L’organisation courante est affichée uniquement si l’utilisateur a accès à plusieurs organisations ou si cela évite une ambiguïté.
```

### 7.1 Cas simple : une seule organisation

Exemple :

```txt id="hxnab5"
Ramalo | Aviron Juniors | Yves
```

L’organisation reste connue techniquement, mais n’est pas affichée en permanence.

### 7.2 Cas multi-organisation

Exemple :

```txt id="wkxrnl"
Ramalo | RCL Lausanne | Aviron Juniors | Yves
```

L’organisation est affichée pour éviter toute confusion.

### 7.3 Changement de contexte

Si l’utilisateur peut changer d’organisation ou de club, le header doit offrir un accès clair à cette action.

Exemples :

```txt id="n0aacw"
Aviron Juniors ▾
```

ou :

```txt id="f33azq"
RCL Lausanne ▾ | Aviron Juniors ▾
```

Le changement de contexte doit être explicite.

## 8. Parcours d’entrée dans l’application

Le parcours général est :

```txt id="yhkc1d"
Ouverture application
-> connexion
-> récupération de l’utilisateur courant
-> récupération des organisations/clubs accessibles
-> choix éventuel du contexte
-> redirection vers l’espace adapté
```

L’application ne doit pas supposer immédiatement un club ou une organisation sans avoir résolu le contexte courant.

## 9. Authentification UX

Le parcours de connexion doit être conçu autour de l’email.

La V1 peut commencer avec le login classique, mais le login par code email doit être prévu rapidement.

### 9.1 Connexion classique

Flux :

```txt id="j1lq4o"
Email
-> mot de passe
-> connexion
-> récupération du contexte
-> redirection
```

### 9.2 Connexion par code email

Fonctionnalité souhaitée rapidement.

Flux cible :

```txt id="m0acdb"
Email
-> envoi d’un code temporaire
-> saisie du code
-> vérification
-> connexion
-> récupération du contexte
-> redirection
```

Cette méthode ne remplace pas le login email / mot de passe. Elle vient en complément pour simplifier l’accès des utilisateurs.

L’interface de login ne doit donc pas être conçue comme si le mot de passe était l’unique mode définitif.

## 10. Choix organisation / club

Après connexion, l’application doit résoudre le contexte.

### 10.1 Une organisation, un club

Le contexte est sélectionné automatiquement.

### 10.2 Une organisation, plusieurs clubs

L’utilisateur peut choisir le club courant, sauf si un dernier club utilisé est connu et encore accessible.

### 10.3 Plusieurs organisations

L’utilisateur choisit d’abord l’organisation, puis le club si nécessaire.

Exemple :

```txt id="bwtcc8"
Choisissez une organisation

[ RCL Lausanne ]
[ Autre organisation ]
```

Puis :

```txt id="tbxd9h"
Choisissez un club

[ Aviron Juniors ]
[ Aviron Seniors ]
```

Une option “Tous les clubs” ne doit être proposée que si les droits API le permettent.

## 11. Redirection après connexion

Règle proposée :

```txt id="nduc4x"
Si un dernier espace utilisé est connu et encore autorisé
-> y retourner

Sinon si l’utilisateur a uniquement accès à l’espace membre
-> ouvrir l’espace membre

Sinon si l’utilisateur a uniquement accès à l’espace gestion
-> ouvrir l’espace gestion

Sinon si l’utilisateur a accès aux deux
-> afficher un choix clair entre espace membre et espace gestion
```

Pour un utilisateur ayant deux casquettes, il est préférable de ne pas le forcer automatiquement dans l’espace gestion.

Exemple d’écran :

```txt id="jtwcrj"
Que voulez-vous faire ?

[ Accéder à mon espace membre ]
Voir mes événements, mes inscriptions et mon profil.

[ Accéder à l’espace gestion ]
Gérer les membres, les événements, les inscriptions et les demandes publiques.
```

## 12. Navigation desktop

Sur desktop et tablette large, une sidebar latérale est adaptée.

Structure possible :

```txt id="x4iqp6"
Accueil

Espace membre
- Événements
- Mes inscriptions
- Mon profil

Gestion
- Tableau de bord
- Membres
- Événements
```

Les entrées doivent être affichées selon les capacités.

Un utilisateur ne devrait pas voir une entrée de menu qui mène systématiquement à une page interdite.

## 13. Navigation mobile

Sur mobile, la navigation doit être compacte.

Recommandation :

```txt id="pekqkk"
Header compact
Bouton menu
Drawer latéral
Contenu principal
```

Pour l’espace membre, une navigation basse peut être envisagée :

```txt id="b7bdew"
Accueil | Événements | Inscriptions | Profil
```

Pour l’espace gestion, un drawer est préférable, car il y aura plus d’entrées et d’actions.

Les listes doivent être affichées sous forme de cartes plutôt que de grandes tables.

## 14. Routes principales pressenties

### 14.1 Authentification

```txt id="jb28w7"
/login
/login/password
/login/email-code
/login/email-code/verify
```

Ces routes peuvent aussi être remplacées par un seul écran `/login` avec plusieurs étapes internes.

### 14.2 Contexte

```txt id="v7k5k1"
/app/select-organization
/app/select-club
/app/home
```

### 14.3 Espace membre

```txt id="e3xjo8"
/app/member
/app/member/events
/app/member/events/:eventId
/app/member/registrations
/app/member/profile
```

### 14.4 Espace gestion

```txt id="jstame"
/app/manage
/app/manage/members
/app/manage/members/new
/app/manage/members/:personId
/app/manage/members/:personId/edit
/app/manage/events
/app/manage/events/new
/app/manage/events/:eventId
/app/manage/events/:eventId/edit
/app/manage/events/:eventId/registrations
/app/manage/events/:eventId/public-registration-requests
```

Les routes exactes pourront être ajustées lors de la conception Angular.

## 15. Tableau de bord membre

Objectif :

```txt id="q3cctq"
Répondre rapidement à :
- qu’est-ce qui arrive bientôt ?
- à quoi suis-je inscrit ?
- où puis-je agir ?
```

Blocs proposés :

```txt id="aqjsvn"
Prochains événements
Mes prochaines inscriptions
Mon club
Actions rapides
```

Actions rapides :

```txt id="darxg9"
Voir les événements
Voir mes inscriptions
Voir mon profil
```

Si l’utilisateur a aussi accès à la gestion :

```txt id="gt497o"
Accéder à l’espace gestion
```

## 16. Liste événements membre

La page permet au membre de consulter les événements accessibles et de s’inscrire.

Données affichées :

* titre ;
* date ;
* heure ;
* lieu ;
* club ;
* type d’événement ;
* places restantes si pertinent ;
* statut d’inscription personnel ;
* action principale.

Actions possibles :

```txt id="i1liro"
Voir détail
S’inscrire
Annuler mon inscription
```

Statuts visibles :

```txt id="srr0ro"
S’inscrire
Déjà inscrit
En liste d’attente
Inscriptions fermées
Événement complet
```

Sur mobile, affichage en cartes.

## 17. Détail événement membre

Le détail événement côté membre doit afficher :

* titre ;
* description ;
* date de début ;
* date de fin ;
* lieu ;
* club ;
* type ;
* capacité si pertinente ;
* statut des inscriptions ;
* statut personnel du membre.

Un bloc clair doit indiquer la situation personnelle :

```txt id="dxcgtf"
Vous êtes inscrit à cet événement.
```

```txt id="pz76ch"
Vous êtes en liste d’attente.
```

```txt id="nyzqeo"
Vous n’êtes pas encore inscrit.
```

```txt id="tom54a"
Vous avez annulé votre inscription.
```

## 18. Mes inscriptions

La page “Mes inscriptions” permet au membre de retrouver ses inscriptions.

Organisation possible :

```txt id="oitwbr"
À venir
Passées
Annulées
```

Données affichées :

* événement ;
* date ;
* lieu ;
* club ;
* statut ;
* action principale.

Actions :

```txt id="qblj23"
Voir l’événement
Annuler mon inscription
```

L’annulation doit demander confirmation.

## 19. Mon profil

La page profil est principalement en lecture seule en V1.

Informations possibles :

* prénom ;
* nom ;
* email ;
* clubs liés ;
* adhésion ;
* responsabilités éventuelles.

Évolutions futures :

* modification partielle du profil ;
* préférences ;
* langue ;
* notifications ;
* modes de connexion ;
* mot de passe.

## 20. Tableau de bord gestion

Le tableau de bord gestion doit donner une vue rapide de ce qui nécessite une action.

Questions auxquelles il doit répondre :

```txt id="c81kbj"
Quels membres nécessitent une action ?
Quels événements arrivent bientôt ?
Y a-t-il des demandes publiques en attente ?
Y a-t-il des inscriptions à surveiller ?
```

Blocs possibles :

```txt id="i2u7nw"
Membres à suivre
Événements à venir
Demandes publiques en attente
Actions rapides
```

Actions rapides :

```txt id="ra6i6m"
Créer un membre
Voir les membres
Créer un événement
Voir les événements
Voir les demandes en attente
```

## 21. Gestion des membres

La gestion des membres est prioritaire en V1.

Elle doit permettre de :

* consulter les membres du club ;
* rechercher un membre ;
* créer un membre ;
* ouvrir le détail d’un membre ;
* modifier les informations principales ;
* consulter l’adhésion ;
* gérer une adhésion de base si le backend le permet.

### 21.1 Liste des membres

Données affichées :

* prénom ;
* nom ;
* email ;
* statut d’adhésion ;
* responsabilités éventuelles ;
* actions.

Colonnes desktop possibles :

```txt id="olcrun"
Nom | Email | Adhésion | Responsabilités | Actions
```

Filtres V1 possibles :

```txt id="mv6gv8"
Tous
Membres actifs
Sans adhésion active
Membres inactifs
Responsables / gestionnaires
```

Recherche :

```txt id="wmh4zb"
Nom ou email
```

Sur mobile, affichage sous forme de cartes.

### 21.2 Création / modification membre

Champs minimum V1 :

```txt id="dllbpa"
Prénom
Nom
Email
```

Sections possibles :

```txt id="ih5lta"
Identité
Coordonnées
Adhésion
```

La section adhésion peut être intégrée dès la création si le backend le permet, ou gérée ensuite depuis le détail membre.

### 21.3 Détail membre

Le détail membre doit répondre à quatre questions :

```txt id="kcqchq"
Qui est cette personne ?
Quelle est son adhésion ?
A-t-elle des responsabilités ?
À quels événements est-elle inscrite ?
```

Sections possibles :

```txt id="i6xfyw"
Identité
Adhésion
Responsabilités
Inscriptions aux événements
Historique / notes
```

Si la personne a été créée depuis une demande publique, cette information peut être affichée :

```txt id="gheq4n"
Créé depuis une demande publique d’inscription
```

## 22. Adhésions et cotisations

Dans le contexte actuel du club, les adhésions ne sont pas limitées dans le temps.

La V1 ne doit donc pas gérer :

* renouvellement d’adhésion ;
* alerte “adhésion à renouveler” ;
* cotisations ;
* rappels de paiement.

La cotisation est actuellement gérée par une application financière séparée.

Cependant, ces notions doivent rester envisageables comme évolutions futures, notamment pour d’autres clubs ou de futurs niveaux de service.

Évolutions futures possibles :

```txt id="nke23g"
Cotisations
Échéances d’adhésion
Renouvellement d’adhésion
Rappels de paiement
Intégration avec une application de gestion financière
```

## 23. Gestion des événements

La gestion des événements est l’autre pilier prioritaire de la V1.

La page événements doit permettre de :

* consulter les événements ;
* rechercher un événement ;
* filtrer par statut ;
* créer un événement ;
* ouvrir le détail ;
* accéder aux inscriptions ;
* accéder aux demandes publiques.

Colonnes desktop possibles :

```txt id="wmvdzu"
Date | Titre | Statut | Inscriptions | Demandes | Actions
```

Si l’utilisateur gère plusieurs clubs, afficher aussi le club.

Filtres V1 possibles :

```txt id="a2oi4w"
À venir
Passés
Brouillons
Publiés
Annulés
Archivés
```

Sur mobile, affichage sous forme de cartes.

## 24. Création / modification événement

Champs attendus :

* titre ;
* description ;
* lieu ;
* date/heure de début ;
* date/heure de fin ;
* événement toute la journée ;
* timezone ;
* capacité ;
* liste d’attente activée ;
* inscription publique activée ;
* début des inscriptions ;
* fin des inscriptions ;
* type d’événement ;
* club lié.

Structure possible du formulaire :

```txt id="jzlcfo"
Informations générales
Date et heure
Inscriptions
Publication
```

Pour la V1, il faudra décider si un événement créé est publié directement ou créé comme brouillon.

## 25. Détail événement gestion

Le détail événement est une page centrale de l’espace gestion.

Il peut être structuré en onglets :

```txt id="q3ejv4"
Informations
Inscriptions
Demandes publiques
```

En-tête :

* titre ;
* date ;
* club ;
* statut ;
* actions principales.

Résumé opérationnel :

```txt id="d6h1eu"
Inscrits : 18 / 20
Liste d’attente : 2
Demandes publiques en attente : 3
Inscriptions ouvertes jusqu’au 5 septembre
```

## 26. Inscriptions événement

L’onglet inscriptions permet de consulter et gérer les inscriptions à l’événement.

Données affichées :

* nom ;
* prénom ;
* email ;
* statut ;
* date d’inscription ;
* adhésion liée si disponible ;
* actions.

Statuts visibles :

```txt id="iebmk7"
Inscrit
Liste d’attente
Annulé
```

Actions possibles :

```txt id="v1uu0w"
Annuler une inscription
Ajouter une inscription manuelle
Voir la personne
```

Pour V1, priorité :

```txt id="g9t00x"
Consulter les inscriptions
Annuler une inscription si permis
Voir la personne
```

## 27. Demandes publiques

Les demandes publiques sont rattachées aux événements en V1.

Elles sont accessibles depuis le détail d’un événement.

Données affichées :

* prénom ;
* nom ;
* email ;
* note éventuelle ;
* statut ;
* date de demande ;
* date de traitement ;
* personne ayant traité la demande ;
* actions.

Statuts visibles :

```txt id="hwyz6l"
En attente
Acceptée
Refusée
```

Actions pour une demande en attente :

```txt id="ajiasy"
Accepter
Refuser
Voir détail
```

Pour une demande traitée :

```txt id="d796fx"
Voir détail
```

### 27.1 Acceptation

L’acceptation doit demander confirmation.

Message :

```txt id="pyxx7l"
Accepter cette demande ?

Une personne sera créée si nécessaire et une inscription sera ajoutée à l’événement.
```

Après succès :

```txt id="zjcb4j"
La demande a été acceptée.
```

### 27.2 Refus

Le refus doit demander confirmation et proposer un motif optionnel.

Message :

```txt id="dnfwlu"
Refuser cette demande ?
```

Champ :

```txt id="rv2cui"
Motif du refus
```

Après succès :

```txt id="fbcmij"
La demande a été refusée.
```

### 27.3 Détail demande

Le détail peut être affiché en modal en V1.

Informations :

* prénom ;
* nom ;
* email ;
* note ;
* événement ;
* statut ;
* date de demande ;
* date de traitement ;
* traité par ;
* motif de refus ;
* personne créée si acceptée ;
* inscription créée si acceptée.

Tri par défaut :

```txt id="totg3f"
Demandes en attente les plus anciennes en premier
```

## 28. États d’écran

Chaque écran doit prévoir des états explicites.

États généraux :

```txt id="erk5ny"
Chargement
Prêt
Vide
Erreur
Action en cours
Accès interdit
Réseau indisponible
```

Exemples de messages :

```txt id="pfpfgn"
Chargement des membres...
```

```txt id="ayje39"
Aucun membre trouvé.
```

```txt id="goqom9"
Chargement des événements...
```

```txt id="auwvmk"
Aucun événement trouvé.
```

```txt id="elv0wv"
Vous n’avez pas les droits nécessaires pour effectuer cette action.
```

```txt id="rybhjw"
Connexion réseau indisponible. Veuillez réessayer.
```

## 29. Erreurs métier

Les erreurs doivent être traduites en messages compréhensibles.

Préférer :

```txt id="ist6au"
Impossible d’accepter cette demande. Elle a peut-être déjà été traitée.
```

plutôt que :

```txt id="ujucqs"
409 Conflict
```

Préférer :

```txt id="d4qu7h"
Votre session a expiré. Veuillez vous reconnecter.
```

plutôt que :

```txt id="p6aac3"
401 Unauthorized
```

Préférer :

```txt id="vca6st"
L’événement est complet. L’inscription n’a pas pu être créée.
```

plutôt que :

```txt id="sxiu9e"
BusinessRuleViolationException
```

## 30. Responsive

Chaque écran doit être pensé en deux versions :

```txt id="yymjxo"
Desktop / tablette large
Mobile
```

### 30.1 Desktop

Sur desktop :

* header complet ;
* sidebar visible ;
* tables pour les listes ;
* filtres visibles ;
* actions principales visibles ;
* actions secondaires dans un menu.

### 30.2 Mobile

Sur mobile :

* header compact ;
* drawer de navigation ;
* listes sous forme de cartes ;
* filtres repliables ;
* actions principales visibles ;
* boutons tactiles suffisamment grands ;
* éviter les tableaux horizontaux.

## 31. Hors périmètre UX V1

Ne sont pas prioritaires en V1 :

* administration système ;
* administration complète d’organisation ;
* widgets publics anonymes ;
* fonctionnement offline métier ;
* synchronisation différée ;
* paiement / cotisations ;
* renouvellement d’adhésion ;
* notifications ;
* messagerie interne ;
* statistiques avancées ;
* exports Excel/PDF ;
* upload de documents ;
* photos ;
* calendrier synchronisé ;
* gestion avancée des rôles.

Ces éléments doivent toutefois rester possibles dans l’architecture future.

## 32. Décisions UX actées

Les décisions suivantes sont actées pour la V1 :

* une seule application Angular principale pour les utilisateurs connectés ;
* deux espaces principaux : membre et gestion ;
* les utilisateurs peuvent cumuler plusieurs casquettes ;
* l’accès aux espaces et actions dépend de capacités ;
* le club courant est toujours affiché dans le header ;
* l’organisation courante est affichée uniquement en contexte multi-organisation ;
* l’espace membre reste simple et non administratif ;
* l’espace gestion repose sur deux piliers : membres et événements ;
* la gestion des membres est prioritaire en V1 ;
* la gestion des événements est prioritaire en V1 ;
* les demandes publiques sont rattachées aux événements en V1 ;
* le login par code email doit être prévu rapidement ;
* la PWA ne fait pas d’offline métier ;
* desktop : sidebar et tables ;
* mobile : drawer et cartes ;
* les adhésions ne nécessitent pas de renouvellement en V1 ;
* les cotisations sont gérées par une application financière séparée ;
* cotisations et renouvellements restent des évolutions futures possibles.
