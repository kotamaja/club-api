# Architecture Angular concrète — Ramalo / RCL — Frontend V1

## 1. Objectif

Le frontend Ramalo V1 doit être une application Angular moderne, maintenable et évolutive.

L’objectif est de construire une base propre pour :

* l’espace membre ;
* l’espace gestion ;
* la gestion des membres ;
* la gestion des événements ;
* les inscriptions ;
* les demandes publiques ;
* le login classique ;
* le futur login par code email ;
* la gestion du contexte organisation / club ;
* les permissions et capacités.

L’architecture doit rester proche des pratiques Angular récentes afin de faciliter la maintenance future.

## 2. Version Angular et stratégie de maintenance

Le frontend doit démarrer sur la version Angular stable la plus récente disponible au moment du développement.

Version cible actuelle :

```txt
Angular 22.1
```

Principes :

* rester proche du cycle de release Angular ;
* éviter de démarrer sur une version déjà vieillissante ;
* privilégier les APIs modernes du framework ;
* limiter la dette technique dès le départ ;
* faciliter les migrations futures ;
* éviter les patterns anciens sauf justification claire.

APIs Angular à privilégier :

* standalone components ;
* signals ;
* computed signals ;
* Signal Forms ;
* resources / `httpResource` lorsque pertinent ;
* lazy loading par routes ;
* guards fonctionnels ;
* interceptors fonctionnels si adaptés ;
* configuration applicative moderne.

## 3. Décisions techniques principales

Décisions actées pour la V1 :

```txt
- Angular récent, actuellement Angular 22.1.
- Angular Material/CDK comme socle UI.
- Application PWA installable.
- Pas d’offline métier.
- Signal Forms par défaut pour les nouveaux formulaires.
- Reactive Forms seulement si un cas précis le justifie.
- Template-driven forms à éviter pour les formulaires métier.
- Signals pour l’état applicatif.
- Services spécialisés par domaine.
- Pas de gros store global en V1.
- État principal des listes stocké dans l’URL.
- Zod pour valider les réponses API importantes.
- Permissions/capacités plutôt que rôles codés en dur.
- Login par code email prévu rapidement.
```

## 4. Structure générale du projet

Structure cible :

```txt
src/app/
├── core/
│   ├── api/
│   ├── auth/
│   ├── capabilities/
│   ├── config/
│   ├── context/
│   ├── errors/
│   ├── guards/
│   ├── interceptors/
│   └── routing/
│
├── shared/
│   ├── ui/
│   ├── forms/
│   ├── layout/
│   ├── list-state/
│   └── utils/
│
└── features/
    ├── auth/
    ├── shell/
    ├── member/
    ├── management/
    ├── members/
    └── events/
```

Principe général :

```txt
core     = infrastructure applicative
shared   = composants et outils réutilisables non métier
features = fonctionnalités métier ou surfaces applicatives
```

## 5. `core/`

Le dossier `core` contient les services fondamentaux de l’application.

Il ne doit pas contenir de composants métier, ni de logique spécifique aux pages membres ou événements.

Responsabilités :

* authentification ;
* session ;
* contexte courant ;
* configuration ;
* client API ;
* erreurs globales ;
* interceptors ;
* guards ;
* capacités ;
* navigation technique.

## 6. `core/api/`

Responsabilité :

* centraliser les accès génériques à l’API ;
* construire les URLs ;
* gérer les modèles génériques de pagination ;
* représenter les erreurs API ;
* éviter de disperser `/api/v1/...` dans les features.

Structure possible :

```txt
core/api/
├── api-client.service.ts
├── api-error.model.ts
├── api-page.model.ts
├── api-pagination.model.ts
├── api-query.model.ts
└── api-url.service.ts
```

### 6.1 `ApiUrlService`

Construit les URLs API à partir d’une configuration centrale.

Exemple d’usage souhaité :

```ts
this.apiUrl.build('/events');
```

À éviter :

```ts
this.http.get(`${environment.apiUrl}/api/v1/events`);
```

Objectif :

```txt
Une seule source de vérité pour l’URL de l’API.
```

### 6.2 `ApiClientService`

Wrapper léger autour de `HttpClient`, si utile.

Responsabilités possibles :

* sérialiser les query params ;
* appliquer des options communes ;
* centraliser certaines conventions API ;
* rester générique.

Il ne doit pas devenir un service métier global.

## 7. `core/config/`

Responsabilité :

* charger et exposer la configuration applicative ;
* connaître l’URL de l’API ;
* connaître la version API ;
* connaître le nom de l’application ;
* activer ou désactiver certaines options selon l’environnement.

Structure possible :

```txt
core/config/
├── app-config.model.ts
├── app-config.service.ts
└── app-config.token.ts
```

Configuration attendue :

```txt
- baseApiUrl
- apiVersion
- appName
- production
- serviceWorkerEnabled
- debugEnabled
```

Le service worker doit être désactivé ou limité en développement.

## 8. `core/auth/`

Responsabilité :

* gérer la session ;
* connecter l’utilisateur ;
* déconnecter l’utilisateur ;
* préparer plusieurs modes de connexion ;
* stocker l’access token si nécessaire ;
* gérer le futur login par code email.

Structure possible :

```txt
core/auth/
├── auth.service.ts
├── session.service.ts
├── token.service.ts
├── passwordless-login.service.ts
├── auth-state.model.ts
├── current-user.model.ts
└── login-result.model.ts
```

## 9. `AuthService`

Responsabilité :

* exposer les méthodes de connexion ;
* appeler les endpoints d’authentification ;
* créer la session côté frontend ;
* gérer le logout ;
* rester compatible avec plusieurs stratégies de login.

Méthodes possibles :

```ts
loginWithPassword(email: string, password: string): Observable<LoginResult>;

requestEmailCode(email: string): Observable<void>;

loginWithEmailCode(email: string, code: string): Observable<LoginResult>;

logout(): Observable<void>;
```

Même si le login par code email n’est pas encore disponible côté API, l’architecture frontend doit l’anticiper.

## 10. `SessionService`

Responsabilité :

* représenter l’état de session ;
* exposer l’utilisateur courant ;
* savoir si l’utilisateur est connecté ;
* initialiser la session au démarrage ;
* nettoyer la session au logout ;
* coordonner la récupération du contexte après login.

État possible :

```ts
type SessionState =
  | { status: 'unknown' }
  | { status: 'anonymous' }
  | { status: 'authenticated'; user: CurrentUser };
```

Signals possibles :

```ts
readonly state = signal<SessionState>({ status: 'unknown' });

readonly isAuthenticated = computed(() => this.state().status === 'authenticated');

readonly currentUser = computed(() => {
  const state = this.state();

  return state.status === 'authenticated' ? state.user : null;
});
```

## 11. `TokenService`

Responsabilité :

* stocker l’access token si nécessaire ;
* fournir l’access token à l’interceptor ;
* supprimer le token au logout.

Principe important :

```txt
Le refresh token doit idéalement rester en cookie httpOnly.
```

Le frontend ne doit pas manipuler directement le refresh token si le backend peut le gérer via cookie sécurisé.

## 12. `PasswordlessLoginService`

Le login par code email doit être prévu comme fonctionnalité proche.

Responsabilité :

* demander l’envoi d’un code ;
* vérifier un code ;
* gérer le renvoi de code ;
* gérer les erreurs spécifiques ;
* exposer l’état du workflow.

État possible :

```ts
type EmailCodeLoginState =
  | { status: 'idle' }
  | { status: 'requesting-code' }
  | { status: 'code-sent'; email: string }
  | { status: 'verifying-code'; email: string }
  | { status: 'authenticated' }
  | { status: 'error'; message: string };
```

Erreurs à prévoir :

* email invalide ;
* code invalide ;
* code expiré ;
* trop de tentatives ;
* trop de demandes de code ;
* problème d’envoi email ;
* erreur réseau.

## 13. `core/context/`

Responsabilité :

* gérer l’organisation courante ;
* gérer le club courant ;
* résoudre le contexte après login ;
* exposer le contexte à l’application ;
* permettre de changer de contexte.

Structure possible :

```txt
core/context/
├── current-context.service.ts
├── context-resolver.service.ts
├── organization-context.service.ts
├── club-context.service.ts
├── current-context.model.ts
├── organization-summary.model.ts
└── club-summary.model.ts
```

## 14. `CurrentContextService`

Responsabilité :

* exposer le contexte courant ;
* savoir si le contexte est résolu ;
* exposer l’organisation active ;
* exposer le club actif ;
* exposer les capacités du contexte ;
* réagir aux changements de contexte.

État possible :

```ts
type CurrentContextState =
  | { status: 'unknown' }
  | { status: 'loading' }
  | { status: 'resolved'; context: CurrentContext }
  | { status: 'needs-organization-selection'; organizations: OrganizationSummary[] }
  | { status: 'needs-club-selection'; clubs: ClubSummary[] }
  | { status: 'no-access' }
  | { status: 'error'; message: string };
```

Signals possibles :

```ts
readonly state = signal<CurrentContextState>({ status: 'unknown' });

readonly currentOrganization = computed(() => {
  const state = this.state();

  return state.status === 'resolved' ? state.context.organization : null;
});

readonly currentClub = computed(() => {
  const state = this.state();

  return state.status === 'resolved' ? state.context.club : null;
});
```

## 15. `ContextResolverService`

Responsabilité :

* orchestrer la résolution du contexte après login ;
* charger les organisations accessibles ;
* charger les clubs accessibles ;
* restaurer le dernier contexte utilisé si valide ;
* déterminer si un choix utilisateur est nécessaire ;
* proposer une destination de redirection.

Ce service évite de mettre trop de logique dans les guards ou dans les composants.

## 16. `OrganizationContextService`

Responsabilité :

* charger les organisations accessibles ;
* sélectionner une organisation ;
* savoir si l’utilisateur est multi-organisation ;
* mémoriser éventuellement le dernier choix.

Règle UX associée :

```txt
L’organisation courante n’est affichée dans le header que si l’utilisateur a accès à plusieurs organisations.
```

## 17. `ClubContextService`

Responsabilité :

* charger les clubs accessibles pour l’organisation courante ;
* sélectionner le club courant ;
* exposer le club courant ;
* mémoriser éventuellement le dernier club utilisé.

Règle UX associée :

```txt
Le club courant est toujours affiché dans le header.
```

## 18. `core/capabilities/`

Responsabilité :

* centraliser les capacités ;
* éviter les tests directs sur les rôles ;
* alimenter la navigation ;
* protéger les routes ;
* masquer les actions non disponibles.

Structure possible :

```txt
core/capabilities/
├── capability.service.ts
├── capability.model.ts
└── capability.directive.ts
```

## 19. `CapabilityService`

Responsabilité :

* lire les capacités du contexte courant ;
* fournir des méthodes simples ;
* être utilisé par les guards, menus et composants.

Méthodes possibles :

```ts
can(capability: Capability): boolean;

canAny(capabilities: Capability[]): boolean;

canAll(capabilities: Capability[]): boolean;
```

Exemples de capacités :

```ts
type Capability =
  | 'canAccessMemberArea'
  | 'canAccessManagementArea'
  | 'canManageMembers'
  | 'canManageMemberships'
  | 'canManageEvents'
  | 'canManageEventRegistrations'
  | 'canReviewPublicRegistrationRequests'
  | 'canManageClubSettings';
```

À éviter :

```ts
role === 'club_manager'
```

À préférer :

```ts
this.capabilityService.can('canManageMembers');
```

## 20. `CapabilityDirective`

Directive possible pour l’affichage conditionnel.

Exemple conceptuel :

```html
<button *appCan="'canManageMembers'">
  Créer un membre
</button>
```

Objectif :

```txt
Centraliser l’affichage conditionnel basé sur les capacités.
```

Selon la version Angular et la syntaxe retenue, cette directive pourra être adaptée à la syntaxe moderne.

## 21. `core/interceptors/`

Responsabilité :

* ajouter les informations nécessaires aux requêtes ;
* gérer l’authentification ;
* ajouter le contexte organisation ;
* transformer certaines erreurs globales.

Structure possible :

```txt
core/interceptors/
├── auth-token.interceptor.ts
├── organization-context.interceptor.ts
├── api-error.interceptor.ts
└── network-error.interceptor.ts
```

## 22. `auth-token.interceptor`

Responsabilité :

* ajouter l’access token aux requêtes API connectées ;
* ignorer les endpoints publics ;
* ignorer les URLs externes ;
* gérer les cas où aucune session n’est active.

## 23. `organization-context.interceptor`

Responsabilité :

* ajouter le header `X-Organization-Id` aux appels API connectés ;
* ne pas l’ajouter tant que le contexte n’est pas résolu ;
* ne pas l’ajouter aux endpoints publics anonymes si non nécessaire.

## 24. `api-error.interceptor`

Responsabilité :

* transformer les erreurs API en objets frontend cohérents ;
* faciliter l’affichage d’erreurs métier ;
* distinguer validation, conflit, accès refusé, non authentifié, serveur.

## 25. `network-error.interceptor`

Responsabilité :

* détecter l’absence réseau ;
* produire une erreur compréhensible ;
* éventuellement alimenter un état global réseau indisponible.

## 26. `core/errors/`

Responsabilité :

* modéliser les erreurs frontend ;
* mapper les erreurs API vers des messages utilisateur ;
* éviter d’afficher directement les codes HTTP.

Structure possible :

```txt
core/errors/
├── app-error.model.ts
├── api-error-mapper.service.ts
├── validation-error.model.ts
└── error-message.service.ts
```

Mapping attendu :

```txt
400 -> requête invalide
401 -> session expirée ou non connecté
403 -> droits insuffisants
404 -> ressource introuvable ou non accessible
409 -> conflit métier
422 -> règle métier non respectée
429 -> trop de tentatives
500 -> erreur serveur
```

## 27. `core/guards/`

Responsabilité :

* protéger les routes publiques/connectées ;
* protéger les routes selon le contexte ;
* protéger les routes selon les capacités.

Structure possible :

```txt
core/guards/
├── auth.guard.ts
├── guest.guard.ts
├── context.guard.ts
└── capability.guard.ts
```

## 28. `authGuard`

Protège les routes connectées :

```txt
/app/**
```

Rôle :

```txt
Vérifier qu’une session utilisateur existe.
Rediriger vers /login si nécessaire.
```

## 29. `guestGuard`

Protège les routes de login.

Rôle :

```txt
Si l’utilisateur est déjà connecté, rediriger vers /app.
```

## 30. `contextGuard`

Protège les routes métier.

Rôle :

```txt
Vérifier que l’organisation et le club courant sont résolus.
Rediriger vers la sélection de contexte si nécessaire.
Rediriger vers no-access si aucun contexte n’est disponible.
```

## 31. `capabilityGuard`

Protège les routes selon les capacités.

Les capacités nécessaires doivent être déclarées dans les métadonnées de route.

Exemple conceptuel :

```ts
{
  path: 'manage/members',
  canActivate: [capabilityGuard],
  data: {
    capabilities: ['canManageMembers'],
  },
}
```

## 32. `core/routing/`

Responsabilité :

* centraliser certaines constantes de routes ;
* gérer la navigation technique ;
* fournir un retour liste simple ;
* éviter les URLs dispersées.

Structure possible :

```txt
core/routing/
├── app-route-paths.ts
├── navigation.service.ts
├── return-navigation.service.ts
└── route-capability.model.ts
```

## 33. `ReturnNavigationService`

Responsabilité limitée :

* gérer un retour simple depuis une page détail ;
* utiliser l’historique navigateur si pertinent ;
* fournir un fallback vers la liste par défaut.

Il ne doit pas mémoriser tous les états des listes.

Principe :

```txt
L’état principal des listes est dans l’URL.
Le service de retour ne remplace pas l’URL.
```

Exemple :

```ts
backOrNavigate(['/app/manage/members']);
```

Comportement attendu :

```txt
Si l’utilisateur vient d’une liste
-> retour navigateur

Sinon
-> navigation vers la liste par défaut
```

## 34. `shared/`

Le dossier `shared` contient les composants, modèles et utilitaires réutilisables, mais non spécifiques à un domaine métier précis.

Il ne doit pas contenir de logique métier forte.

Structure possible :

```txt
shared/
├── ui/
├── forms/
├── layout/
├── list-state/
└── utils/
```

## 35. `shared/ui/`

Composants UI génériques.

Structure possible :

```txt
shared/ui/
├── loading-state/
├── empty-state/
├── error-state/
├── forbidden-state/
├── confirm-dialog/
├── page-header/
├── status-badge/
└── action-menu/
```

Exemples :

* `LoadingStateComponent`;
* `EmptyStateComponent`;
* `ErrorStateComponent`;
* `ForbiddenStateComponent`;
* `ConfirmDialogComponent`;
* `PageHeaderComponent`;
* `StatusBadgeComponent`;
* `ActionMenuComponent`.

## 36. `shared/forms/`

Responsabilité :

* fournir des composants de formulaire réutilisables ;
* gérer l’affichage des erreurs ;
* fournir des helpers Signal Forms ;
* centraliser les états de soumission.

Structure possible :

```txt
shared/forms/
├── field-error/
├── form-actions/
├── form-section/
├── form-submit-state.model.ts
└── signal-form-utils.ts
```

Règle actée :

```txt
Signal Forms d’abord.
Reactive Forms si nécessaire.
Template-driven forms à éviter pour les formulaires métier.
```

Formulaires concernés :

* login classique ;
* login par code email ;
* sélection organisation / club ;
* création / modification membre ;
* création / modification événement ;
* refus d’une demande publique ;
* filtres de listes si pertinent.

## 37. `shared/list-state/`

Responsabilité :

* gérer simplement l’état des listes via l’URL ;
* lire les query params ;
* écrire les query params ;
* appliquer des valeurs par défaut ;
* normaliser recherche, filtres, tri, pagination ;
* éviter une implémentation complexe comme un store global.

Structure possible :

```txt
shared/list-state/
├── list-query-state.model.ts
├── list-query-state.service.ts
├── list-query-param-utils.ts
└── pagination.model.ts
```

Principe retenu :

```txt
URL d’abord.
Pas de store global de navigation.
Helper léger seulement.
```

## 38. État des listes dans l’URL

Les pages de liste doivent conserver leur état lorsque l’utilisateur ouvre un détail puis revient à la liste.

Pages concernées :

* liste des membres ;
* liste des événements ;
* liste des inscriptions ;
* liste des demandes publiques ;
* liste des événements côté membre ;
* liste de mes inscriptions.

L’état principal doit être représenté dans les query params :

```txt
- search
- status
- sort
- direction
- page
- perPage
```

Exemple :

```txt
/app/manage/members?search=dupont&status=active&sort=lastname&direction=asc&page=2
```

Retour attendu :

```txt
liste filtrée
-> détail
-> retour navigateur
-> liste restaurée avec les mêmes query params
```

## 39. Recherche, tri et pagination

Convention V1 :

* recherche texte : mise à jour débouncée avec `replaceUrl` ;
* filtres : query params ;
* tri : query params ;
* pagination : query params ;
* reset filtres : retour aux valeurs par défaut ;
* éviter de polluer l’historique navigateur avec chaque frappe.

À éviter :

```txt
d
du
dup
dupo
dupon
dupont
```

comme entrées successives dans l’historique.

## 40. Modèles de query par feature

Chaque liste garde son propre modèle de query.

Exemple membres :

```ts
type MemberListQuery = {
  search: string;
  status: 'all' | 'active' | 'inactive' | 'withoutMembership';
  sort: 'lastname' | 'email' | 'createdAt';
  direction: 'asc' | 'desc';
  page: number;
  perPage: number;
};
```

Exemple événements :

```ts
type EventListQuery = {
  search: string;
  period: 'upcoming' | 'past' | 'all';
  status: 'all' | 'draft' | 'published' | 'cancelled' | 'archived';
  sort: 'startsAt' | 'title';
  direction: 'asc' | 'desc';
  page: number;
  perPage: number;
};
```

Le helper partagé facilite la lecture/écriture, mais ne remplace pas les modèles spécifiques.

## 41. `features/`

Le dossier `features` contient les surfaces fonctionnelles et les fonctionnalités métier.

Structure retenue :

```txt
features/
├── auth/
├── shell/
├── member/
├── management/
├── members/
└── events/
```

## 42. `features/auth/`

Contient les pages et composants de connexion.

Structure possible :

```txt
features/auth/
├── pages/
│   ├── login-page/
│   ├── password-login-page/
│   ├── email-code-request-page/
│   └── email-code-verify-page/
├── components/
│   ├── email-step-form/
│   ├── password-login-form/
│   └── email-code-form/
└── auth.routes.ts
```

V1 minimale :

* `login-page`;
* `password-login-form`.

Préparation proche terme :

* `email-code-request-page`;
* `email-code-verify-page`;
* `email-code-form`.

## 43. `features/shell/`

Contient le layout connecté.

Structure possible :

```txt
features/shell/
├── pages/
│   ├── app-entry-page/
│   ├── app-home-page/
│   ├── select-organization-page/
│   ├── select-club-page/
│   └── no-access-page/
├── components/
│   ├── connected-shell/
│   ├── app-header/
│   ├── desktop-sidebar/
│   ├── mobile-drawer/
│   └── context-switcher/
└── shell.routes.ts
```

## 44. `ConnectedShellComponent`

Responsabilité :

* afficher le header ;
* afficher la navigation desktop ou mobile ;
* afficher le contenu via `router-outlet` ;
* réagir au contexte courant ;
* afficher le club courant ;
* afficher l’organisation seulement si nécessaire.

## 45. `AppHeaderComponent`

Responsabilité :

* logo / nom Ramalo ;
* club courant ;
* organisation courante si multi-organisation ;
* menu utilisateur ;
* logout ;
* accès au changement de contexte.

Règle UX :

```txt
Le club courant est toujours affiché.
L’organisation courante est affichée uniquement si l’utilisateur a accès à plusieurs organisations.
```

## 46. `DesktopSidebarComponent`

Responsabilité :

* afficher la navigation principale sur desktop ;
* distinguer espace membre et espace gestion ;
* afficher les entrées selon les capacités.

## 47. `MobileDrawerComponent`

Responsabilité :

* afficher la navigation sur mobile ;
* reprendre les mêmes règles de capacité que la sidebar ;
* fermer le drawer après navigation.

## 48. `ContextSwitcherComponent`

Responsabilité :

* permettre de changer d’organisation ou de club ;
* afficher seulement les choix pertinents ;
* rendre explicite le changement de contexte.

## 49. `features/management/`

Contient l’espace gestion comme zone fonctionnelle.

Structure possible :

```txt
features/management/
├── pages/
│   └── management-dashboard-page/
├── components/
│   ├── management-quick-actions/
│   ├── management-summary-cards/
│   ├── members-follow-up-card/
│   └── pending-public-requests-card/
└── management.routes.ts
```

Ce dossier orchestre l’espace gestion.

Il ne doit pas absorber toute la logique des membres et des événements.

## 50. `features/members/`

Contient la gestion des membres du club.

Attention :

```txt
features/member  = mon espace membre
features/members = gestion des membres du club
```

Structure possible :

```txt
features/members/
├── pages/
│   ├── member-list-page/
│   ├── member-detail-page/
│   ├── member-create-page/
│   └── member-edit-page/
├── components/
│   ├── member-table/
│   ├── member-card-list/
│   ├── member-form/
│   ├── member-summary-card/
│   ├── member-membership-panel/
│   └── member-event-registrations-panel/
├── data/
│   ├── member-list.resource.ts
│   ├── member-detail.resource.ts
│   ├── member-write.service.ts
│   ├── member.model.ts
│   ├── member.schema.ts
│   ├── member.commands.ts
│   └── member-list-query.model.ts
└── members.routes.ts
```

## 51. Services `features/members`

### 51.1 `MemberListResource`

Responsabilité :

* charger la liste des membres ;
* gérer pagination, recherche, filtres, tri ;
* lire l’état depuis l’URL ;
* exposer loading/error/data.

### 51.2 `MemberDetailResource`

Responsabilité :

* charger le détail d’un membre ;
* exposer identité, adhésion, responsabilités, inscriptions.

### 51.3 `MemberWriteService`

Responsabilité :

* créer un membre ;
* modifier les informations principales ;
* gérer l’adhésion de base si disponible.

Méthodes possibles :

```ts
createMember(command: CreateMemberCommand): Observable<MemberDetail>;

updateMember(personId: string, command: UpdateMemberCommand): Observable<MemberDetail>;
```

## 52. `features/events/`

Contient la gestion des événements.

Structure possible :

```txt
features/events/
├── pages/
│   ├── event-list-page/
│   ├── event-detail-page/
│   ├── event-create-page/
│   └── event-edit-page/
├── components/
│   ├── event-table/
│   ├── event-card-list/
│   ├── event-form/
│   ├── event-summary-panel/
│   ├── event-registrations-panel/
│   └── public-registration-requests-panel/
├── data/
│   ├── event-list.resource.ts
│   ├── event-detail.resource.ts
│   ├── event-write.service.ts
│   ├── event-registration.service.ts
│   ├── public-registration-request.service.ts
│   ├── event.model.ts
│   ├── event.schema.ts
│   ├── event.commands.ts
│   ├── event-list-query.model.ts
│   ├── event-registration.model.ts
│   └── public-registration-request.model.ts
└── events.routes.ts
```

## 53. Services `features/events`

### 53.1 `EventListResource`

Responsabilité :

* charger la liste des événements côté gestion ;
* gérer recherche, filtres, tri, pagination ;
* lire l’état depuis l’URL ;
* exposer loading/error/data.

### 53.2 `EventDetailResource`

Responsabilité :

* charger le détail d’un événement ;
* exposer informations principales ;
* exposer les indicateurs opérationnels si disponibles.

### 53.3 `EventWriteService`

Responsabilité :

* créer un événement ;
* modifier un événement ;
* annuler ou archiver un événement si disponible.

Méthodes possibles :

```ts
createEvent(command: CreateEventCommand): Observable<EventDetail>;

updateEvent(eventId: string, command: UpdateEventCommand): Observable<EventDetail>;
```

### 53.4 `EventRegistrationService`

Responsabilité :

* charger les inscriptions d’un événement ;
* annuler une inscription ;
* ajouter une inscription manuelle si disponible plus tard.

Méthodes possibles :

```ts
listRegistrations(eventId: string, query: EventRegistrationQuery): Observable<ApiPage<EventRegistrationListItem>>;

cancelRegistration(eventId: string, registrationId: string): Observable<void>;
```

### 53.5 `PublicRegistrationRequestService`

Responsabilité :

* charger les demandes publiques d’un événement ;
* accepter une demande ;
* refuser une demande ;
* charger le détail d’une demande.

Méthodes possibles :

```ts
listRequests(eventId: string, query: PublicRegistrationRequestQuery): Observable<ApiPage<PublicRegistrationRequestListItem>>;

getRequest(requestId: string): Observable<PublicRegistrationRequestDetail>;

acceptRequest(requestId: string): Observable<PublicRegistrationRequestDetail>;

rejectRequest(requestId: string, reason: string | null): Observable<PublicRegistrationRequestDetail>;
```

## 54. `features/member/`

Contient l’espace membre de l’utilisateur connecté.

Attention à la distinction :

```txt
features/member  = mon espace membre
features/members = gestion des membres
```

Structure possible :

```txt
features/member/
├── pages/
│   ├── member-dashboard-page/
│   ├── member-event-list-page/
│   ├── member-event-detail-page/
│   ├── my-registrations-page/
│   └── my-profile-page/
├── components/
│   ├── my-upcoming-events-card/
│   ├── my-registrations-card/
│   ├── member-event-card-list/
│   └── my-profile-summary/
├── data/
│   ├── member-dashboard.resource.ts
│   ├── member-events.resource.ts
│   ├── my-registrations.resource.ts
│   ├── my-profile.resource.ts
│   └── member-registration.service.ts
└── member.routes.ts
```

## 55. Services `features/member`

### 55.1 `MemberDashboardResource`

Responsabilité :

* charger les prochains événements ;
* charger les prochaines inscriptions ;
* charger le résumé du profil/club.

### 55.2 `MemberEventsResource`

Responsabilité :

* charger les événements accessibles au membre ;
* inclure le statut personnel d’inscription si disponible.

### 55.3 `MyRegistrationsResource`

Responsabilité :

* charger les inscriptions de l’utilisateur connecté.

### 55.4 `MyProfileResource`

Responsabilité :

* charger les informations personnelles de l’utilisateur connecté.

### 55.5 `MemberRegistrationService`

Responsabilité :

* inscrire le membre connecté à un événement ;
* annuler son inscription.

Méthodes possibles :

```ts
registerToEvent(eventId: string): Observable<MyEventRegistration>;

cancelMyRegistration(eventId: string, registrationId: string): Observable<void>;
```

## 56. Modèles TypeScript et Zod

Pour chaque feature métier, prévoir :

```txt
model.ts
schema.ts
commands.ts
query.ts
```

Exemples :

```txt
features/members/data/member.model.ts
features/members/data/member.schema.ts
features/members/data/member.commands.ts
features/members/data/member-list-query.model.ts
```

```txt
features/events/data/event.model.ts
features/events/data/event.schema.ts
features/events/data/event.commands.ts
features/events/data/event-list-query.model.ts
```

Objectifs :

* valider les réponses API importantes ;
* éviter les hypothèses implicites ;
* centraliser les types ;
* sécuriser les évolutions API ;
* fournir des modèles lisibles pour l’UI.

Zod sert surtout à valider :

* les réponses API ;
* les DTO frontend ;
* les structures complexes reçues du backend.

## 57. États explicites par page

Chaque page importante doit avoir un état explicite.

Exemple :

```ts
type PageState<T> =
  | { status: 'loading' }
  | { status: 'ready'; data: T }
  | { status: 'empty' }
  | { status: 'error'; message: string }
  | { status: 'forbidden' };
```

Pour les actions :

```ts
type ActionState =
  | { status: 'idle' }
  | { status: 'submitting' }
  | { status: 'success' }
  | { status: 'error'; message: string };
```

Objectif :

```txt
Éviter les booléens dispersés comme loading/error/saving sans cohérence.
```

## 58. Formulaires

Les nouveaux formulaires doivent être conçus en priorité avec Signal Forms.

Formulaires concernés :

* login classique ;
* login par code email ;
* vérification de code email ;
* sélection organisation ;
* sélection club ;
* création membre ;
* modification membre ;
* création événement ;
* modification événement ;
* refus d’une demande publique ;
* filtres de listes si pertinent.

Règle :

```txt
Signal Forms d’abord.
Reactive Forms si nécessaire.
Template-driven forms à éviter pour le métier.
```

Reactive Forms reste acceptable si :

* une intégration spécifique est plus simple ;
* une limitation ponctuelle des Signal Forms bloque un cas ;
* un composant tiers impose une API plus adaptée aux Reactive Forms.

## 59. Routes

Les routes principales retenues sont :

```txt
/login
/app
/app/home
/app/select-organization
/app/select-club
/app/member
/app/member/events
/app/member/events/:eventId
/app/member/registrations
/app/member/profile
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

Organisation recommandée :

```txt
app.routes.ts
features/auth/auth.routes.ts
features/shell/shell.routes.ts
features/member/member.routes.ts
features/management/management.routes.ts
features/members/members.routes.ts
features/events/events.routes.ts
```

## 60. Ordre d’implémentation recommandé

### Phase 1 — Socle Angular

* création du projet ;
* Angular Material/CDK ;
* PWA ;
* structure `core/shared/features` ;
* routes principales ;
* layout minimal ;
* composants UI génériques.

### Phase 2 — Auth classique

* login email / mot de passe ;
* `AuthService`;
* `SessionService`;
* `TokenService`;
* `authGuard`;
* `guestGuard`;
* logout ;
* session expirée.

### Phase 3 — Contexte courant

* `/me`;
* `/me/organizations`;
* `/me/current-context`;
* `CurrentContextService`;
* `ContextResolverService`;
* sélection organisation ;
* sélection club ;
* header contextuel ;
* interceptor `X-Organization-Id`.

### Phase 4 — Capacités

* `CapabilityService`;
* `CapabilityGuard`;
* navigation conditionnelle ;
* directive `appCan`;
* capacités initialement dérivées si l’API fine n’est pas encore prête.

### Phase 5 — Login par code email

Cette phase peut être avancée, car le login par code email est souhaité rapidement.

* demande de code ;
* vérification du code ;
* renvoi de code ;
* erreurs spécifiques ;
* rate limit ;
* expiration du code ;
* intégration session.

### Phase 6 — Gestion des membres

* liste membres ;
* état de liste dans l’URL ;
* détail membre ;
* création membre ;
* modification membre ;
* adhésion de base si disponible.

### Phase 7 — Gestion des événements

* liste événements ;
* état de liste dans l’URL ;
* détail événement ;
* création événement ;
* modification événement.

### Phase 8 — Inscriptions et demandes publiques

* inscriptions événement ;
* demandes publiques ;
* détail demande en modal ;
* acceptation ;
* refus avec motif ;
* confirmations.

### Phase 9 — Espace membre

* dashboard membre ;
* événements accessibles ;
* détail événement côté membre ;
* inscription du membre connecté ;
* mes inscriptions ;
* profil en lecture seule.

## 61. Principes à respecter pendant l’implémentation

Pendant le développement, respecter les principes suivants :

```txt
- Ne pas introduire de store global lourd sans besoin réel.
- Ne pas coder l’UI directement sur des rôles.
- Ne pas disperser les URLs API dans les composants.
- Ne pas mélanger features/member et features/members.
- Ne pas cacher l’état des listes uniquement dans des services.
- Ne pas afficher les erreurs HTTP brutes à l’utilisateur.
- Ne pas concevoir les formulaires comme si Reactive Forms était le choix par défaut.
- Ne pas rendre le login email/mot de passe trop rigide, car le login par code email arrive rapidement.
```

## 62. Décisions actées

Décisions finales pour cette architecture V1 :

* Angular 22.1 ou version stable récente au démarrage ;
* Angular Material/CDK ;
* PWA installable ;
* pas d’offline métier ;
* Signal Forms par défaut ;
* Reactive Forms seulement si nécessaire ;
* signals pour l’état applicatif ;
* services spécialisés ;
* pas de store global lourd ;
* état des listes dans l’URL ;
* helper léger pour les query params ;
* Zod pour valider les réponses API importantes ;
* capacités centralisées ;
* guards simples et composables ;
* login par code email prévu rapidement ;
* membres et événements comme deux piliers prioritaires ;
* espace membre et espace gestion dans une seule application Angular ;
* composants publics anonymes séparés plus tard.
