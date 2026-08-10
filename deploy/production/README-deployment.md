# Déploiement de l'application

Ce répertoire contient les scripts utilisés pour construire, déployer et restaurer une version de l'application.

Les trois scripts sont :

```text
deploy/production/
├── build-release.sh
├── deploy.sh
└── rollback.sh
```

Le processus est volontairement séparé en trois étapes distinctes :

1. `build-release.sh` construit un artefact de release.
2. `deploy.sh` installe et active cet artefact sur l'environnement cible.
3. `rollback.sh` permet de réactiver rapidement une release précédente.

---

# 1. Utilisation

## 1.1. `build-release.sh`

`build-release.sh` est exécuté sur la machine de développement.

Le build utilise Docker Compose pour exécuter Symfony, Composer, les tests et Angular. La machine hôte n'a donc pas besoin de PHP, Composer, Node.js ou npm installés directement.

Le script produit :

```text
dist/
├── rcl-<release>.tar.gz
└── rcl-<release>.tar.gz.sha256
```

L'archive contient uniquement les fichiers nécessaires à l'exécution de l'application. Les dépendances PHP ne sont pas incluses dans l'archive ; elles seront installées par `deploy.sh`.

### Aide

```bash
./deploy/production/build-release.sh --help
```

ou :

```bash
./deploy/production/build-release.sh -h
```

### Build officiel

```bash
./deploy/production/build-release.sh
```

Un build officiel impose les conditions suivantes :

- le working tree Git doit être propre ;
- `HEAD` doit correspondre exactement à un tag Git ;
- les tests Symfony doivent réussir ;
- la validation Composer doit réussir.

Le nom de la release correspond au tag Git.

Exemple :

```text
v1.0.0
```

produira :

```text
dist/rcl-v1.0.0.tar.gz
dist/rcl-v1.0.0.tar.gz.sha256
```

### Build de développement

```bash
./deploy/production/build-release.sh --dev
```

Le mode `--dev` permet :

- de construire depuis la branche courante ;
- d'avoir un working tree non propre ;
- de construire sans tag Git ;
- de continuer malgré un échec des tests ;
- de continuer malgré un échec de `composer validate`.

Le nom généré est de la forme :

```text
dev-YYYYMMDD-HHMMSS
```

Exemple :

```text
rcl-dev-20260810-190000.tar.gz
```

Un artefact `dev-*` ne doit pas être utilisé comme release officielle de production.

### Build de développement sans tests

```bash
./deploy/production/build-release.sh --dev --no-tests
```

`--no-tests` est volontairement interdit hors du mode `--dev`.

La commande suivante échoue donc :

```bash
./deploy/production/build-release.sh --no-tests
```

---

## 1.2. `deploy.sh`

`deploy.sh` installe une archive créée par `build-release.sh`.

Le script attend une structure de déploiement de ce type :

```text
<PROJECT_ROOT>/
├── incoming/
├── releases/
├── shared/
│   ├── .env.prod.local
│   ├── config/
│   │   └── jwt/
│   │       ├── private.pem
│   │       └── public.pem
│   ├── public/
│   │   └── .htaccess
│   └── var/
│       └── log/
├── current
└── current_release
```

L'archive et son checksum doivent être placés dans :

```text
<PROJECT_ROOT>/incoming/
```

Par exemple :

```text
incoming/
├── rcl-v1.0.0.tar.gz
└── rcl-v1.0.0.tar.gz.sha256
```

### Aide

```bash
./deploy.sh --help
```

ou :

```bash
./deploy.sh -h
```

### Déploiement normal

La variable `PROJECT_ROOT` est obligatoire :

```bash
PROJECT_ROOT="/path/to/application" \
./deploy.sh rcl-v1.0.0.tar.gz
```

Dans ce mode :

- PHP et Composer sont exécutés directement sur la machine cible ;
- la connexion à la base de données est vérifiée ;
- les migrations Doctrine sont vérifiées puis exécutées ;
- la nouvelle release n'est activée qu'après réussite de toutes les étapes précédentes.

### Déploiement de test local avec Docker

Pour tester `deploy.sh` sur une machine de développement ne disposant pas de PHP ou Composer directement :

```bash
DEV_PROJECT_ROOT="$HOME/Projects/project" \
PROJECT_ROOT="$HOME/tmp/rcl-deploy-test" \
./deploy.sh \
    --dev \
    rcl-dev-20260810-190000.tar.gz
```

`DEV_PROJECT_ROOT` désigne la racine du dépôt de développement contenant :

```text
docker-compose.yml
```

Dans ce mode, Docker Compose fournit PHP et Composer.

Le répertoire de déploiement reste cependant `PROJECT_ROOT`. Le script monte toute cette arborescence dans le conteneur afin que les liens relatifs vers `shared/` fonctionnent exactement comme sur le serveur cible.

### Déploiement sans migrations

Il est possible de désactiver explicitement les contrôles et migrations de base de données :

```bash
PROJECT_ROOT="/path/to/application" \
./deploy.sh \
    --no-db-migrations \
    rcl-v1.0.0.tar.gz
```

Cette option est disponible aussi bien en production qu'en mode `--dev`.

Le script affiche alors un avertissement et demande une confirmation :

```text
WARNING: Database checks and migrations will NOT be executed.

Continue deployment? [y/N]
```

Seules les réponses `y` ou `Y` permettent de continuer.

### Désactiver la confirmation

Pour une exécution non interactive :

```bash
PROJECT_ROOT="/path/to/application" \
./deploy.sh \
    --no-db-migrations \
    --force \
    rcl-v1.0.0.tar.gz
```

`--force` ne signifie pas « ignorer les erreurs ».

Il ne désactive aucune validation et ne permet pas au script de continuer après une erreur.

Son rôle est uniquement de supprimer la confirmation interactive associée à une action explicitement demandée comme risquée.

### Exemple de test local sans migrations

```bash
DEV_PROJECT_ROOT="$HOME/Projects/project" \
PROJECT_ROOT="$HOME/tmp/rcl-deploy-test" \
./deploy.sh \
    --dev \
    --no-db-migrations \
    --force \
    rcl-dev-20260810-190000.tar.gz
```

---

## 1.3. `rollback.sh`

`rollback.sh` réactive une release déjà présente dans :

```text
<PROJECT_ROOT>/releases/
```

Il ne reconstruit rien et n'exécute ni PHP, ni Composer, ni Docker.

Il ne modifie jamais la base de données.

### Aide

```bash
./rollback.sh --help
```

ou :

```bash
./rollback.sh -h
```

### Rollback

```bash
PROJECT_ROOT="/path/to/application" \
./rollback.sh v1.0.0
```

Le script indique la release actuellement active et la release cible, puis demande confirmation.

### Rollback sans confirmation

```bash
PROJECT_ROOT="/path/to/application" \
./rollback.sh \
    --force \
    v1.0.0
```

### Important : base de données

Un rollback ne revient jamais automatiquement sur les migrations Doctrine.

Il ne fait que restaurer le code d'une release précédente.

Pour cette raison, les migrations de production devraient autant que possible rester compatibles avec la release précédente, au minimum pendant la période où un rollback de code doit rester possible.

---

# 2. Fonctionnement des scripts

## 2.1. Vue d'ensemble

Le principe général est celui des releases immuables.

Chaque déploiement possède son propre répertoire :

```text
releases/
├── v1.0.0/
├── v1.1.0/
└── v1.2.0/
```

Une release installée ne doit plus être modifiée manuellement.

Les éléments persistants entre les releases sont stockés dans :

```text
shared/
```

Deux liens symboliques indiquent la version active :

```text
current
current_release
```

Exemple :

```text
current         -> releases/v1.2.0/public
current_release -> releases/v1.2.0
```

`current` correspond à la racine HTTP publique.

`current_release` pointe vers la racine complète de la release et peut être utilisé pour les commandes CLI, tâches cron ou opérations d'administration.

---

## 2.2. Fonctionnement de `build-release.sh`

Le script commence par déterminer automatiquement la racine du dépôt Git puis les répertoires Symfony et Angular.

Il démarre ensuite les services Docker nécessaires :

```text
mysql-dev
php
node
```

### Backend

Les dépendances Symfony sont installées dans l'environnement Docker :

```bash
composer install
```

Si les tests sont activés, le script crée la base de test si nécessaire puis lance PHPUnit.

Pour un build officiel, un échec des tests interrompt immédiatement le build.

Pour un build `--dev`, l'échec est signalé mais le build peut continuer.

### Validation Composer

Le script exécute :

```bash
composer validate --strict --no-check-publish
```

Une incohérence entre `composer.json` et `composer.lock` bloque un build officiel.

En mode `--dev`, elle génère seulement un avertissement.

### Frontend

Le script installe les dépendances exactement selon le lock npm :

```bash
npm ci
```

puis génère le build Angular de production :

```bash
npm run build
```

Le résultat Angular est ensuite copié dans le répertoire public Symfony.

La release finale contient donc notamment :

```text
public/
├── index.php
├── index.html
├── main-*.js
├── styles-*.css
└── ...
```

`index.php` sert Symfony/API Platform.

`index.html` sert l'application Angular.

### Contenu de l'artefact

Le script copie les éléments runtime Symfony nécessaires :

```text
bin/
config/
migrations/
src/
templates/
public/
composer.json
composer.lock
symfony.lock
.env
```

Il retire explicitement les éléments qui ne doivent pas être inclus :

```text
vendor/
var/
tests/
config/jwt/
.env.local
.env.dev
.env.test
.env.prod.local
phpunit.dist.xml
```

Les secrets et fichiers persistants ne font donc jamais partie de l'artefact.

### Archive

La release est compressée dans :

```text
dist/rcl-<release>.tar.gz
```

Puis un checksum SHA-256 est généré :

```text
dist/rcl-<release>.tar.gz.sha256
```

Le `trap` du script arrête finalement les services Docker démarrés pour le build.

---

## 2.3. Fonctionnement de `deploy.sh`

Le script reçoit le nom d'une archive placée dans `incoming/`.

### 1. Validation de l'environnement

Le script vérifie notamment :

- `PROJECT_ROOT` ;
- l'existence de l'archive ;
- l'existence du checksum ;
- que la release cible n'existe pas déjà ;
- la présence des ressources obligatoires sous `shared/`.

### 2. Vérification de l'archive

Le checksum SHA-256 est contrôlé avant extraction.

Cela permet de détecter un artefact incomplet ou altéré avant de commencer le déploiement.

### 3. Extraction

L'archive est extraite dans :

```text
releases/<release>/
```

Le script vérifie ensuite la présence des fichiers essentiels :

```text
composer.json
composer.lock
bin/console
public/index.php
public/index.html
```

### 4. Liens vers les ressources partagées

Chaque release reçoit des liens symboliques vers les ressources persistantes :

```text
.env.prod.local
    -> ../../shared/.env.prod.local

config/jwt
    -> ../../shared/config/jwt

var/log
    -> ../../../shared/var/log

public/.htaccess
    -> ../../../shared/public/.htaccess
```

Ainsi :

- les secrets restent hors des releases ;
- les clés JWT restent identiques entre deux déploiements ;
- les logs persistent ;
- la configuration Apache peut être gérée indépendamment des artefacts applicatifs.

Le cache Symfony reste volontairement propre à chaque release.

### 5. Installation Composer

Le serveur installe uniquement les dépendances de production :

```bash
composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction
```

`vendor/` n'a donc pas besoin d'être transporté depuis la machine de build.

### 6. Environnement Symfony

Le déploiement s'exécute avec :

```text
APP_ENV=prod
APP_DEBUG=0
```

Puis :

```bash
composer dump-env prod
```

génère le fichier Symfony optimisé :

```text
.env.local.php
```

### 7. Cache Symfony

Le script valide Symfony puis reconstruit le cache de production :

```bash
php bin/console about
php bin/console cache:clear
php bin/console cache:warmup
```

### 8. Base de données

Par défaut, le script :

1. vérifie la connexion ;
2. affiche l'état des migrations ;
3. exécute les migrations.

Conceptuellement :

```bash
php bin/console dbal:run-sql "SELECT 1"
php bin/console doctrine:migrations:status
php bin/console doctrine:migrations:migrate --no-interaction
```

Cette étape peut être explicitement désactivée avec :

```text
--no-db-migrations
```

### 9. Activation

La nouvelle release n'est activée qu'à la fin.

Le script repointe :

```text
current
current_release
```

vers la nouvelle release.

Jusqu'à cette étape, la release précédemment active continue donc d'être celle référencée par les symlinks.

---

## 2.4. Mode `--dev` de `deploy.sh`

Le mode `--dev` ne change pas la structure du déploiement.

Son seul rôle est de permettre de tester localement le comportement du script lorsque PHP et Composer ne sont disponibles que dans Docker.

Deux répertoires différents sont alors utilisés :

```text
DEV_PROJECT_ROOT
```

contient le dépôt de développement et son `docker-compose.yml`.

```text
PROJECT_ROOT
```

représente l'environnement de déploiement à tester.

Exemple :

```text
DEV_PROJECT_ROOT/
├── docker-compose.yml
├── symfony/
└── frontend/

PROJECT_ROOT/
├── incoming/
├── releases/
└── shared/
```

Le conteneur PHP monte l'ensemble de `PROJECT_ROOT` sous :

```text
/deployment
```

et travaille dans :

```text
/deployment/releases/<release>
```

Ce montage de toute l'arborescence est important.

Monter uniquement le répertoire de la release casserait les liens relatifs vers :

```text
../../shared/
```

Le mode `--dev` force également explicitement :

```text
APP_ENV=prod
APP_DEBUG=0
```

dans les conteneurs temporaires afin que `composer install --no-dev` et les commandes Symfony reproduisent le comportement d'un véritable déploiement de production.

---

## 2.5. Fonctionnement de `rollback.sh`

Le rollback est volontairement indépendant de Symfony.

Il n'utilise :

- ni PHP ;
- ni Composer ;
- ni Docker ;
- ni Doctrine.

Le script vérifie simplement :

1. que `PROJECT_ROOT` existe ;
2. que la release cible existe ;
3. que cette release contient les fichiers essentiels ;
4. que la release cible n'est pas déjà active.

Après confirmation, il repointe :

```text
current
current_release
```

vers la release demandée.

Cette simplicité rend le rollback rapide et limite les dépendances nécessaires au moment où un problème de production doit être corrigé.

---

# 3. Ressources partagées et secrets

Les secrets ne doivent jamais être ajoutés au dépôt Git ni à une archive de release.

Ils sont conservés dans :

```text
shared/
```

Notamment :

```text
shared/.env.prod.local
shared/config/jwt/private.pem
shared/config/jwt/public.pem
```

Les permissions recommandées pour les clés JWT sont :

```text
private.pem  600
public.pem   644
```

---

# 4. Workflow recommandé pour une release officielle

Le workflow recommandé est :

```text
développement
    ↓
tests
    ↓
création du tag Git
    ↓
checkout/clone propre du tag
    ↓
build-release.sh
    ↓
rcl-<tag>.tar.gz
rcl-<tag>.tar.gz.sha256
    ↓
transfert dans incoming/
    ↓
deploy.sh
    ↓
validation
    ↓
migrations
    ↓
activation de la release
```

En cas de problème après activation :

```text
rollback.sh <release-précédente>
```

Le rollback ne modifiant pas la base de données, la compatibilité des migrations avec la version précédente doit être prise en compte lors de leur conception.
