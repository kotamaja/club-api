# Cahier des charges frontend — Ramalo / RCL — V1

## 1. Objectif général

Le frontend Ramalo V1 est une application web responsive destinée aux utilisateurs authentifiés d’une organisation ou d’un club.

L’application doit permettre aux utilisateurs de consulter leurs informations personnelles, de participer à la vie du club, et, pour les personnes disposant de droits de gestion, d’administrer certains éléments du club comme les événements, les inscriptions et les demandes publiques.

La V1 ne couvre pas l’administration système ni l’administration complète des organisations. Ces aspects pourront être ajoutés plus tard.

## 2. Plateforme cible

Le frontend doit fonctionner comme une application web moderne, responsive et installable comme PWA.

Plateformes à supporter :

* desktop : Chrome, Firefox, Edge, Safari récents ;
* mobile : iOS Safari, Android Chrome, Android Firefox ;
* tablette : iPad Safari et navigateurs Android récents.

L’application doit rester utilisable comme un site web classique, même si l’utilisateur ne l’installe pas comme PWA.

La PWA est une amélioration de l’expérience utilisateur, mais ne doit pas être une condition nécessaire au fonctionnement de l’application.

## 3. PWA et offline

La V1 sera une PWA installable, mais ne prévoit pas de fonctionnement offline métier.

Cela signifie :

* application installable sur mobile ou desktop ;
* cache possible du shell applicatif ;
* accès aux données métier uniquement via l’API ;
* actions métier nécessitant une connexion réseau ;
* pas de saisie offline ;
* pas de file d’attente locale ;
* pas de synchronisation différée ;
* pas de résolution de conflits.

En cas d’absence réseau, l’application doit afficher un message clair et éviter de faire croire qu’une action a été enregistrée si elle n’a pas été confirmée par l’API.

## 4. Choix techniques pressentis

Le frontend principal sera réalisé avec Angular, dans la dernière version stable disponible au moment du démarrage.

Les choix techniques pressentis sont :

* Angular récent ;
* application standalone ;
* signals Angular ;
* services par feature ;
* `httpResource` lorsque pertinent ;
* Reactive Forms ;
* validation des réponses API avec Zod ;
* Angular Material + Angular CDK comme socle UI ;
* SCSS custom léger pour l’identité visuelle ;
* PWA via les outils Angular ;
* pas de store global lourd au départ ;
* pas de fonctionnement offline métier.

Angular Material / CDK est privilégié pour les composants structurants :

* dialogs ;
* menus ;
* snackbars ;
* formulaires ;
* tables ;
* overlays ;
* accessibilité ;
* navigation responsive.

## 5. Applications et surfaces

La solution sera découpée conceptuellement en deux surfaces principales.

### 5.1 Application Angular principale

L’application principale est destinée aux utilisateurs authentifiés.

Elle couvre en V1 :

* l’espace membre ;
* l’espace gestion club.

Elle ne couvre pas encore :

* l’administration système ;
* l’administration avancée d’organisation ;
* les widgets publics anonymes.

### 5.2 Composants publics intégrables

Les fonctionnalités destinées aux visiteurs anonymes, comme un formulaire public de demande d’inscription à un événement, ne seront pas intégrées directement dans le frontend principal.

Elles seront plutôt prévues plus tard sous forme de composants web Angular intégrables dans un site public.

Exemple futur :

```html
<ramalo-event-registration-request event-id="01K..."></ramalo-event-registration-request>
```

Ces composants publics devront être légers, isolés du back-office, et utilisables dans un site web externe.

## 6. Profils utilisateurs

Les profils principaux identifiés sont les suivants.

### 6.1 SystemAdmin

L’administrateur système administre la plateforme.

Il pourra à terme :

* créer des organisations ;
* activer ou désactiver une organisation ;
* gérer les plans de service ;
* gérer des limites globales ;
* intervenir pour du support technique.

Ce rôle n’est pas dans le périmètre V1 du frontend principal.

### 6.2 OrganizationAdmin

L’administrateur d’organisation administre une organisation.

Il pourra à terme :

* gérer les clubs de l’organisation ;
* gérer les paramètres généraux ;
* gérer les utilisateurs de l’organisation ;
* attribuer des responsabilités à des gestionnaires de club ;
* avoir une vue transversale sur plusieurs clubs.

Ce rôle n’est pas prioritaire dans la V1, même si l’architecture doit rester compatible avec son ajout futur.

### 6.3 ClubMember

Un ClubMember est une personne membre d’un club.

Il s’agit d’un statut métier lié à l’appartenance au club.

Un ClubMember peut :

* consulter son profil ;
* consulter ses adhésions ;
* voir les événements accessibles ;
* s’inscrire à un événement ;
* consulter ses inscriptions ;
* éventuellement annuler une inscription si les règles le permettent.

### 6.4 ClubOperator

Un ClubOperator est une personne ayant au moins un droit opérationnel limité sur un club.

Ce n’est pas forcément un rôle exclusif. Un ClubOperator est souvent aussi ClubMember.

Exemples :

* responsable des inscriptions ;
* responsable des événements ;
* moniteur pouvant gérer les participants d’un événement ;
* personne autorisée à traiter les demandes publiques.

Un ClubOperator peut avoir certains droits de gestion sans pouvoir gérer les membres du club.

### 6.5 ClubManager

Un ClubManager est une personne ayant des droits larges de gestion sur un club.

Il peut typiquement gérer :

* les événements ;
* les inscriptions ;
* les demandes publiques ;
* les membres ;
* les adhésions ;
* certains paramètres du club.

Comme ClubOperator, ClubManager n’est pas exclusif de ClubMember. Une personne peut être à la fois membre du club et gestionnaire du club.

## 7. Principe fondamental : statuts et responsabilités cumulables

Les statuts métier et les responsabilités de gestion sont cumulables.

Une personne peut être :

* ClubMember uniquement ;
* ClubMember + ClubOperator ;
* ClubMember + ClubManager ;
* OrganizationAdmin sans forcément être membre d’un club ;
* OrganizationAdmin + ClubManager dans certains cas.

La distinction importante est la suivante :

* ClubMember décrit l’appartenance métier au club ;
* ClubOperator décrit un accès partiel à l’espace gestion ;
* ClubManager décrit un accès large à l’espace gestion.

Ces notions ne doivent pas être modélisées comme des catégories exclusives.

## 8. Permissions et capacités

Le frontend ne doit pas être conçu autour de simples conditions de rôle codées en dur.

À terme, l’affichage des menus, pages et actions devra dépendre de capacités ou permissions.

Exemples de capacités :

* `canAccessMemberArea`;
* `canAccessManagementArea`;
* `canManageEvents`;
* `canManageEventRegistrations`;
* `canReviewPublicRegistrationRequests`;
* `canManageMembers`;
* `canManageMemberships`;
* `canManageClubSettings`.

Même si l’API ne fournit pas encore ces droits fins en V1, le frontend doit être structuré pour pouvoir les intégrer plus tard sans refonte majeure.

## 9. Espaces de l’application

### 9.1 Espace membre

Accessible aux ClubMembers.

Fonctionnalités V1 envisagées :

* tableau de bord personnel ;
* profil personnel ;
* liste des clubs / adhésions ;
* événements accessibles ;
* inscription à un événement ;
* liste de mes inscriptions ;
* annulation éventuelle d’une inscription.

L’espace membre doit être simple, orienté utilisateur final.

### 9.2 Espace gestion

Accessible à toute personne ayant au moins une permission de gestion.

L’espace gestion ne doit pas être réservé uniquement à un rôle global ClubManager.

Fonctionnalités V1 envisagées :

* tableau de bord de gestion club ;
* liste des événements ;
* détail d’un événement ;
* création / modification d’événement ;
* liste des inscriptions à un événement ;
* gestion des inscriptions ;
* liste des demandes publiques d’inscription ;
* détail d’une demande publique ;
* acceptation d’une demande publique ;
* refus d’une demande publique.

Les sections visibles dans l’espace gestion dépendront des permissions.

Exemple :

* une personne avec `canManageEvents` voit les événements ;
* une personne avec `canManageEventRegistrations` voit les inscriptions ;
* une personne avec `canReviewPublicRegistrationRequests` voit les demandes publiques ;
* une personne avec `canManageMembers` voit les membres.

## 10. Fonctionnalité issue de l’API actuelle : demandes publiques

Le backend dispose déjà d’un workflow complet pour les demandes publiques d’inscription à un événement.

La V1 du frontend de gestion devra permettre :

* afficher les demandes publiques d’un événement ;
* filtrer les demandes par statut ;
* rechercher par nom ou email ;
* trier par date de demande ;
* ouvrir le détail d’une demande ;
* accepter une demande ;
* refuser une demande avec motif ;
* rafraîchir la liste après traitement.

Les statuts principaux sont :

* pending ;
* accepted ;
* rejected.

Les demandes publiques concernent principalement les ClubOperators ou ClubManagers ayant la permission de revue.

## 11. PublicVisitor

Le PublicVisitor est un visiteur non authentifié.

Il peut soumettre une demande publique d’inscription à un événement.

Cette fonctionnalité ne sera pas incluse dans l’application principale V1. Elle sera prévue plus tard via un composant web intégrable.

Le composant public devra utiliser l’endpoint public de l’API, déjà protégé côté backend par :

* honeypot ;
* règles métier anti-doublon ;
* blocage des emails déjà qualifiés ;
* rate limiting.

## 12. Multi-tenant et contexte courant

L’application doit tenir compte du caractère multi-tenant de l’API.

Le frontend devra gérer :

* utilisateur courant ;
* organisation courante ;
* club courant éventuel ;
* permissions/capacités dans le contexte courant.

À terme, après connexion :

* récupérer le contexte courant ;
* afficher les organisations accessibles si plusieurs existent ;
* permettre de changer d’organisation ;
* éventuellement permettre de changer de club ;
* adapter les menus et routes selon les capacités.

Les identifiants importants devraient autant que possible être présents dans l’URL afin de permettre :

* rechargement de page ;
* partage de lien ;
* ouverture dans un nouvel onglet ;
* navigation navigateur cohérente.

## 13. Authentification

Le frontend devra s’aligner sur le backend existant.

### 13.1 Connexion classique

La V1 peut commencer avec le mode de connexion actuellement prévu côté API :

* login par email et mot de passe ;
* access token utilisé par le frontend ;
* refresh token idéalement en cookie httpOnly ;
* refresh silencieux lorsque possible ;
* logout propre ;
* gestion claire de l’expiration de session.

Le frontend devra gérer :

* utilisateur non connecté ;
* session expirée ;
* utilisateur désactivé ;
* échec de refresh token ;
* retour à la page de login.

### 13.2 Connexion sans mot de passe par code email

À terme, l’application devra aussi pouvoir proposer une connexion sans mot de passe.

Cette méthode ne remplace pas le login classique. Elle constitue une alternative destinée à simplifier l’accès des utilisateurs, notamment les membres ou gestionnaires occasionnels qui ne souhaitent pas gérer un mot de passe supplémentaire.

Le principe attendu est :

1. l’utilisateur saisit son adresse email ;
2. le système envoie un code temporaire par email ;
3. l’utilisateur saisit ce code dans un formulaire ;
4. si le code est valide, l’utilisateur est connecté ;
5. une session normale est créée côté frontend/API.

Cette méthode peut être nommée :

* passwordless login ;
* login par code email ;
* email OTP ;
* one-time code ;
* magic code.

Elle se distingue du magic link classique, où l’utilisateur clique directement sur un lien reçu par email.

### 13.3 Exigences futures pour le login par code email

L’API actuelle ne permet pas encore cette méthode, mais le frontend doit être conçu pour pouvoir l’ajouter plus tard sans refonte majeure.

Le frontend ne doit donc pas supposer que l’authentification se limite définitivement à :

```txt
email + mot de passe
```

Il doit pouvoir accueillir plusieurs stratégies de connexion :

* email + mot de passe ;
* email + code temporaire ;
* éventuellement d’autres méthodes plus tard.

L’écran de connexion devra pouvoir évoluer vers deux choix :

* se connecter avec un mot de passe ;
* recevoir un code par email.

Ou vers un flux en deux étapes :

1. saisie de l’email ;
2. choix ou proposition d’une méthode de connexion.

### 13.4 Points de sécurité à prévoir côté API

La connexion par code email devra être conçue avec des protections spécifiques :

* code à usage unique ;
* expiration courte, par exemple 10 minutes ;
* nombre d’essais limité ;
* rate limit par IP ;
* rate limit par email ;
* message neutre pour éviter l’énumération d’emails ;
* invalidation du code après succès ;
* journalisation des tentatives ;
* protection contre les demandes répétées de codes ;
* absence d’information publique indiquant si l’email existe ou non.

Le message public devrait rester neutre, par exemple :

```txt
Si un compte existe pour cet email, un code a été envoyé.
```

et non :

```txt
Cet email n’existe pas.
```

## 14. Gestion d’état

La V1 ne devrait pas utiliser de store global lourd.

Approche recommandée :

* signals Angular ;
* services par feature ;
* états explicites par page ;
* données globales limitées.

Données globales probables :

* utilisateur courant ;
* organisation courante ;
* club courant ;
* permissions/capacités ;
* configuration applicative ;
* état de session.

Les données métier spécifiques, comme la liste des événements ou des demandes publiques, peuvent rester dans les services/features correspondants.

## 15. Formulaires et erreurs

Le frontend devra utiliser une stratégie cohérente pour les formulaires.

Principes :

* Reactive Forms ;
* validation côté client pour les erreurs simples ;
* validation des réponses API avec Zod ;
* affichage clair des erreurs API ;
* distinction entre erreurs de validation, erreurs métier, conflits, erreurs réseau et erreurs d’authentification.

Types d’erreurs à gérer :

* 400 requête invalide ;
* 401 non authentifié ;
* 403 non autorisé, plus tard ;
* 404 ressource non visible ou inexistante ;
* 409 conflit ;
* 422 règle métier non respectée ;
* 429 rate limit ;
* 500 erreur serveur.

Pour les formulaires d’authentification, il faudra aussi gérer :

* identifiants invalides ;
* code email invalide ;
* code expiré ;
* trop de tentatives ;
* trop de demandes de code ;
* session expirée ;
* retour à la connexion.

## 16. Internationalisation

La V1 peut commencer en français.

Cependant, l’architecture doit rester compatible avec une future internationalisation.

Contexte probable :

* français d’abord ;
* allemand possible plus tard ;
* anglais possible plus tard.

Il faudra décider ultérieurement entre Angular i18n natif et une solution de traduction dynamique comme ngx-translate.

## 17. Dates et fuseaux horaires

La gestion des événements impose une attention particulière aux dates.

Le frontend devra gérer clairement :

* dates de début et fin d’événement ;
* événements all-day ;
* timezone de l’événement ;
* affichage localisé ;
* saisie date/heure adaptée mobile ;
* absence d’ambiguïté entre UTC et heure locale.

Le fuseau par défaut attendu est probablement `Europe/Zurich`, mais le modèle backend prévoit déjà une timezone par événement.

## 18. Accessibilité et responsive

L’application doit être utilisable sur desktop, tablette et mobile.

Exigences générales :

* navigation responsive ;
* boutons adaptés au tactile ;
* formulaires lisibles sur mobile ;
* tables adaptées aux petits écrans ;
* navigation clavier raisonnable ;
* labels explicites ;
* messages d’erreur accessibles ;
* contraste suffisant.

Angular Material/CDK est recommandé pour faciliter cette base, mais l’accessibilité doit rester une exigence explicite.

## 19. Photos et uploads futurs

La V1 ne prévoit pas de fonctionnalité de prise de photos.

Cependant, l’architecture frontend doit rester compatible avec de futures fonctionnalités d’acquisition ou d’upload d’images depuis mobile.

Les choix techniques V1 ne doivent pas empêcher :

* sélection d’image depuis un appareil ;
* prise de photo via l’interface native du navigateur ;
* prévisualisation d’image ;
* compression/redimensionnement côté client ;
* upload vers l’API ;
* usage sur iOS, Android et navigateurs desktop modernes.

Pour une future version, la première approche recommandée serait l’usage d’un champ fichier compatible caméra :

```html
<input type="file" accept="image/*" capture="environment">
```

Une caméra intégrée via `getUserMedia()` pourrait être étudiée plus tard si le besoin utilisateur le justifie.

## 20. Environnements et configuration

Le frontend devra prévoir plusieurs environnements :

* développement ;
* staging ;
* production.

Configuration attendue :

* URL de l’API ;
* version API ;
* activation ou non du service worker ;
* nom de l’application ;
* paramètres de debug ;
* éventuellement configuration PWA.

Le service worker devrait être désactivé ou limité en développement afin d’éviter des comportements de cache gênants.

## 21. Priorités V1

Priorité fonctionnelle :

1. espace gestion club ;
2. événements ;
3. inscriptions aux événements ;
4. demandes publiques d’inscription ;
5. espace membre ;
6. inscription d’un membre connecté à un événement.

Priorité technique :

1. application Angular principale ;
2. routing propre ;
3. auth et contexte courant ;
4. structure par features ;
5. UI responsive ;
6. permissions/capacités préparées ;
7. PWA installable ;
8. pas d’offline métier ;
9. architecture d’authentification compatible avec plusieurs modes de connexion.

## 22. Hors périmètre V1

Ne sont pas dans le périmètre V1 :

* administration système ;
* administration complète d’organisation ;
* composants publics intégrables ;
* fonctionnement offline métier ;
* synchronisation différée ;
* gestion de photos ;
* upload de documents ;
* paiement / cotisations ;
* internationalisation complète ;
* login par code email si l’API ne le supporte pas encore.

Cependant, ces éléments doivent être gardés en tête dans les choix d’architecture afin de ne pas bloquer leur ajout futur.

## 23. Décisions actées

Les décisions suivantes sont actées pour le moment :

* Angular sera utilisé pour le frontend principal ;
* l’application sera une PWA installable ;
* la V1 ne fera pas d’offline métier ;
* l’application devra fonctionner sur iOS, Android et navigateurs desktop modernes ;
* Angular Material/CDK est le choix UI recommandé ;
* le frontend principal est destiné aux utilisateurs authentifiés ;
* les widgets publics anonymes seront séparés ;
* ClubMember, ClubOperator et ClubManager sont des notions cumulables ;
* l’accès à l’espace gestion dépendra de permissions/capacités ;
* la V1 se concentrera sur ClubManager / ClubOperator et ClubMember ;
* les fonctionnalités de photo/upload ne sont pas dans la V1 mais doivent rester possibles plus tard ;
* le login classique email/mot de passe reste supporté ;
* une connexion sans mot de passe par code email est souhaitée à terme ;
* l’architecture frontend d’authentification doit rester compatible avec plusieurs modes de connexion.
