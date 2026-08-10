# Déploiement RCL sur Hébergement De Production

## 1. Objectif

Ce document décrit la procédure de déploiement de RCL sur un hébergement mutualisé Hébergement De Production.

L'application est composée de :

* un backend Symfony 8 / API Platform ;
* un frontend Angular ;
* une base de données MariaDB/MySQL fournie par Hébergement De Production.

Le déploiement repose sur des releases versionnées et immuables, avec des données de configuration persistantes placées dans un répertoire `shared`.

L'objectif est d'abord de disposer d'une procédure manuelle fiable et reproductible.

Lorsque cette procédure aura été complètement validée en production, elle pourra être automatisée.

---

# 2. Contraintes de l'Hébergement De Production

L'environnement actuellement disponible sur Hébergement De Production fournit :

```text
PHP      8.4.x
Composer 2.9.x
Git      2.30.x
```

Node.js et npm ne sont pas disponibles :

```text
node: command not found
npm: command not found
```

Docker n'est pas disponible sur l'hébergement mutualisé.

En conséquence :

* PHP et Composer peuvent être utilisés directement sur Hébergement De Production ;
* les commandes Symfony peuvent être exécutées sur Hébergement De Production ;
* les migrations Doctrine peuvent être exécutées sur Hébergement De Production ;
* Angular ne peut pas être compilé sur Hébergement De Production ;
* aucune image Docker ne peut être construite ou exécutée sur Hébergement De Production.

Tous les builds nécessitant Node.js, npm ou Docker doivent donc être effectués en amont, sur la machine locale ou éventuellement dans une CI externe.

RCL utilise PHP 8.4 aussi bien en développement qu'en production.

---

# 3. Principe général

Le déploiement est séparé en deux phases.

```text
MACHINE LOCALE
──────────────

sources
   ↓
tests
   ↓
build Angular
   ↓
préparation de la release
   ↓
création d'une archive .tar.gz
   ↓
transfert vers Hébergement De Production


HÉBERGEMENT DE PRODUCTION
──────────

réception de l'archive
   ↓
création de releases/<version>
   ↓
installation Composer
   ↓
liaison avec shared/
   ↓
préparation Symfony
   ↓
migrations
   ↓
vérifications
   ↓
activation de la release
```

La machine Hébergement De Production ne réalise donc pas de build frontend.

---

# 4. Structure générale sur Hébergement De Production

Le projet suit cette structure :

```text
~/sites/<site-rcl>/
├── current -> releases/<version>/public
├── current_release -> releases/<version>
├── releases/
│   ├── <release-1>/
│   ├── <release-2>/
│   └── ...
└── shared/
    ├── .env.prod.local
    ├── config/
    │   └── jwt/
    │       ├── private.pem
    │       └── public.pem
    ├── public/
    │   └── .htaccess
    └── var/
        └── log/
```

## Rôle des répertoires

### `releases/`

Contient les différentes versions déployées de l'application.

Une release ne doit plus être modifiée une fois activée.

Toute nouvelle version de l'application doit créer une nouvelle release.

### `shared/`

Contient les données qui doivent survivre aux changements de release.

Il contient notamment :

* la configuration de production ;
* les secrets ;
* les clés JWT ;
* les logs ;
* la configuration Apache spécifique à l'hébergement.

### `current`

Lien symbolique vers le répertoire `public/` de la release active.

Exemple :

```text
current -> releases/2026-08-08-001/public
```

Le domaine ou sous-domaine Hébergement De Production pointe vers ce répertoire.

### `current_release`

Lien symbolique vers la racine de la release active.

Exemple :

```text
current_release -> releases/2026-08-08-001
```

Il est notamment utilisé pour les commandes CLI et les tâches cron.

---

# 5. Contenu d'une release

L'archive produite localement doit contenir uniquement les fichiers nécessaires au fonctionnement de l'application.

Structure indicative :

```text
release/
├── bin/
├── config/
├── migrations/
├── public/
├── src/
├── templates/
├── composer.json
├── composer.lock
├── symfony.lock
└── .env
```

D'autres répertoires applicatifs pourront naturellement être présents s'ils sont nécessaires à RCL.

---

# 6. Fichiers exclus de l'archive

L'archive ne doit normalement pas contenir les éléments suivants :

```text
.git/
node_modules/
vendor/
tests/
var/cache/
var/log/
var/share/

.env.local
.env.prod.local

config/jwt/private.pem
config/jwt/public.pem

.editorconfig
.env.dev
.env.test
.gitignore
phpunit.dist.xml
```

Cette liste pourra être ajustée si certains fichiers deviennent nécessaires au runtime.

---

# 7. Gestion de `vendor/`

Le répertoire :

```text
vendor/
```

n'est pas inclus dans l'archive locale.

Composer étant disponible sur Hébergement De Production, les dépendances PHP sont installées directement dans chaque nouvelle release.

Conceptuellement :

```text
archive transférée
      ↓
extraction
      ↓
composer install --no-dev
      ↓
vendor/
```

Cela garantit que les dépendances PHP sont installées dans l'environnement qui les exécutera réellement.

---

# 8. Frontend Angular

Node.js et npm ne sont pas disponibles sur Hébergement De Production.

Le frontend Angular doit donc obligatoirement être construit en local.

La release transférée doit déjà contenir le résultat du build Angular.

Le résultat final doit être intégré dans :

```text
public/
```

de façon à obtenir une structure de ce type :

```text
public/
├── index.php
├── index.html
├── main-*.js
├── styles-*.css
├── assets/
└── ...
```

`index.php` est le point d'entrée Symfony.

`index.html` est le point d'entrée Angular.

---

# 9. Configuration Apache

Sur l'hébergement mutualisé Hébergement De Production, la configuration globale Apache et le VirtualHost sont gérés par Hébergement De Production.

La partie contrôlée par RCL repose sur :

```text
public/.htaccess
```

La version de référence est conservée dans Git :

```text
deploy/production/.htaccess
```

Sur le serveur, elle est installée dans :

```text
shared/public/.htaccess
```

Chaque release utilise ensuite un lien symbolique :

```text
release/public/.htaccess
    -> ../../../shared/public/.htaccess
```

Le `.htaccess` doit assurer les règles suivantes :

```text
/api/*  → Symfony / API Platform
/*      → Angular
```

Les fichiers physiques existants sont servis directement.

Configuration de référence :

```apache
DirectoryIndex index.html index.php

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    RewriteRule ^api(/.*)?$ index.php [QSA,L]

    RewriteRule ^ index.html [L]
</IfModule>
```

---

# 10. Configuration de production

La configuration sensible n'est jamais stockée dans Git.

Elle est placée sur Hébergement De Production dans :

```text
shared/.env.prod.local
```

Chaque release contient le lien :

```text
.env.prod.local -> ../../shared/.env.prod.local
```

Exemples de valeurs pouvant se trouver dans ce fichier :

```text
APP_SECRET
DATABASE_URL
JWT_PASSPHRASE
CORS_ALLOW_ORIGIN
```

D'autres secrets pourront être ajoutés selon les fonctionnalités de RCL.

---

# 11. JWT

Les clés JWT sont persistantes entre les releases.

Elles sont stockées dans :

```text
shared/config/jwt/
```

avec :

```text
private.pem
public.pem
```

Chaque release utilise :

```text
config/jwt -> ../../shared/config/jwt
```

Permissions souhaitées :

```text
private.pem  600
public.pem   644
```

La clé privée ne doit jamais être stockée dans Git.

---

# 12. Répertoire `var`

Le contenu runtime de Symfony reste généralement propre à chaque release.

Structure :

```text
var/
├── cache/
├── share/
└── log -> ../../../shared/var/log
```

## `var/cache`

Cache Symfony propre à la release.

Il n'est pas partagé.

## `var/share`

Peut contenir notamment certains pools de cache Symfony.

Il est considéré comme du runtime propre à la release.

Il n'est pas partagé.

## `var/log`

Les logs doivent survivre aux changements de release.

Ils sont donc stockés dans :

```text
shared/var/log/
```

et liés depuis chaque release :

```text
var/log -> ../../../shared/var/log
```

Les permissions `777` doivent être évitées autant que possible.

Les permissions exactes seront déterminées en fonction de l'utilisateur PHP et des contraintes Hébergement De Production.

---

# 13. Création de la release locale

Le processus local doit fonctionner à partir d'une version exacte du code.

À terme, cette version pourra être identifiée par :

* un tag Git ;
* un numéro de version ;
* ou un identifiant de release.

Exemple :

```text
2026-08-08-001
```

La phase locale effectue conceptuellement :

```text
1. sélectionner la version du code
2. vérifier l'état du dépôt
3. exécuter les tests
4. construire Angular
5. préparer le contenu de la release
6. exclure les fichiers de développement
7. créer l'archive
```

Exemple d'artefact :

```text
rcl-2026-08-08-001.tar.gz
```

Cette archive est considérée comme immuable.

---

# 14. Transfert vers Hébergement De Production

L'archive est transférée vers l'hébergement Hébergement De Production.

Le mécanisme exact pourra être :

```text
scp
rsync
```

ou un mécanisme équivalent.

Le choix définitif sera fait lors de l'automatisation.

Git installé sur Hébergement De Production reste disponible comme outil d'administration ou de dépannage, mais le déploiement normal ne dépend pas d'un `git clone` effectué directement en production.

---

# 15. Installation d'une release sur Hébergement De Production

Pour chaque nouvelle release :

```text
1. créer releases/<version>
2. extraire l'archive
3. créer les liens vers shared/
4. installer les dépendances Composer
5. préparer l'environnement Symfony de production
6. générer/réchauffer le cache
7. vérifier l'application
8. lancer les migrations Doctrine
9. effectuer les smoke tests
10. activer la release
```

---

# 16. Liens `shared`

Les liens suivants doivent être créés pour chaque nouvelle release.

## Configuration

```text
.env.prod.local
    -> ../../shared/.env.prod.local
```

## JWT

```text
config/jwt
    -> ../../shared/config/jwt
```

## Logs

```text
var/log
    -> ../../../shared/var/log
```

## Apache

```text
public/.htaccess
    -> ../../../shared/public/.htaccess
```

---

# 17. Installation Composer

Depuis la racine de la nouvelle release :

```text
composer install --no-dev --optimize-autoloader
```

Les options exactes utilisées en production pourront encore être affinées.

---

# 18. Environnement Symfony

Symfony doit fonctionner avec :

```text
APP_ENV=prod
APP_DEBUG=0
```

La génération de :

```text
.env.local.php
```

sera réalisée avec :

```text
composer dump-env prod
```

Cette étape doit être effectuée après la mise en place du lien vers :

```text
shared/.env.prod.local
```

L'ordre exact sera validé lors du premier déploiement RCL complet.

---

# 19. Cache Symfony

Le cache appartient à la release.

Il peut donc être supprimé et reconstruit avant activation.

Les commandes prévues sont notamment :

```text
php bin/console cache:clear
php bin/console cache:warmup
```

La release active n'est pas impactée pendant cette opération puisque la nouvelle release n'est pas encore référencée par `current`.

---

# 20. Migrations Doctrine

Avant activation de la nouvelle release :

```text
php bin/console doctrine:migrations:status
```

puis :

```text
php bin/console doctrine:migrations:migrate --no-interaction
```

Les migrations doivent être conçues autant que possible pour être compatibles avec la release précédente pendant la courte période précédant la bascule.

Une attention particulière doit être portée aux migrations destructives.

---

# 21. Smoke tests

Avant activation de la release, plusieurs vérifications doivent être réalisées.

Au minimum :

```text
Symfony démarre correctement
la configuration prod est valide
la connexion à la base fonctionne
les migrations sont appliquées
le cache peut être créé
```

Une fois l'accès HTTP à une release non active correctement défini, des tests supplémentaires pourront vérifier :

```text
/
 /api
/api/v1/me
/authentification
```

Le mécanisme exact de smoke test HTTP avant bascule reste à définir.

---

# 22. Activation

La release ne devient visible qu'après validation complète.

La bascule consiste conceptuellement à modifier :

```text
current
current_release
```

pour qu'ils pointent vers la nouvelle release.

Avant :

```text
current
    -> releases/2026-08-01-001/public

current_release
    -> releases/2026-08-01-001
```

Après :

```text
current
    -> releases/2026-08-08-001/public

current_release
    -> releases/2026-08-08-001
```

Cette opération intervient à la toute fin du déploiement.

---

# 23. Rollback

Tant que l'ancienne release est conservée, il est possible de revenir rapidement au code précédent en repositionnant les liens :

```text
current
current_release
```

vers l'ancienne release.

Important :

```text
rollback code ≠ rollback base de données
```

Une migration déjà appliquée n'est pas automatiquement annulée lorsqu'on revient à une ancienne release.

Les migrations doivent donc être conçues en tenant compte de cette contrainte.

---

# 24. Cron et commandes CLI

Les commandes CLI doivent utiliser :

```text
current_release
```

et non :

```text
current
```

car `current` pointe vers le répertoire `public`.

Exemple conceptuel :

```text
cd ~/sites/<site-rcl>/current_release
APP_ENV=prod APP_DEBUG=0 php bin/console <commande>
```

Ainsi les tâches planifiées utilisent automatiquement la release active.

---

# 25. Fichiers de déploiement versionnés dans Git

Le dépôt RCL contient :

```text
deploy/
└── production/
    ├── .htaccess
    ├── README.md
    ├── deploy.sh
    └── rollback.sh
```

## `.htaccess`

Version de référence de la configuration Apache spécifique à Hébergement De Production.

## `README.md`

Documentation de référence du processus de déploiement.

## `deploy.sh`

Futur script chargé d'installer et d'activer une release sur Hébergement De Production.

Il ne sera développé qu'une fois la procédure manuelle complètement validée.

## `rollback.sh`

Futur script chargé de revenir à la release précédente.

---

# 26. Secrets non versionnés

Les éléments suivants ne doivent jamais entrer dans Git :

```text
shared/.env.prod.local

shared/config/jwt/private.pem
shared/config/jwt/public.pem

mots de passe
tokens
clés API
secrets applicatifs
```

Le dépôt contient uniquement la définition de la procédure et les fichiers non sensibles nécessaires au déploiement.

---

# 27. Principe d'immutabilité

Une release activée est considérée comme immuable.

On évite donc :

```text
modifier src/ directement
modifier vendor/ manuellement
modifier public/ manuellement
modifier la configuration spécifique à une release
```

Une modification applicative produit une nouvelle release.

Les données persistantes sont exclusivement placées dans `shared/` ou dans la base de données.

---

# 28. Nettoyage des anciennes releases

Le nettoyage automatique n'est pas encore défini.

À terme, plusieurs anciennes releases devront être conservées afin de permettre un rollback rapide.

Une politique possible pourra par exemple conserver :

```text
release active
release précédente
quelques releases antérieures
```

Cette politique sera définie lors de l'automatisation.

---

# 29. Automatisation future

L'automatisation sera volontairement séparée en deux parties.

## Script local

Responsabilités prévues :

```text
vérifier Git
lancer les tests
builder Angular
préparer l'arborescence
créer l'archive
transférer l'archive
déclencher éventuellement le déploiement distant
```

## Script Hébergement De Production

Responsabilités prévues :

```text
extraire l'archive
créer la release
créer les symlinks shared
composer install
préparer Symfony
lancer les migrations
effectuer les vérifications
basculer current
basculer current_release
```

L'automatisation ne doit être développée qu'après validation manuelle de chaque étape.

---

# 30. Résumé

Architecture :

```text
LOCAL
────────────────────────
PHP 8.4
Docker
Node/npm
Angular build
tests
création .tar.gz

           │
           │ transfert
           ▼

HÉBERGEMENT DE PRODUCTION
────────────────────────
PHP 8.4
Composer
Git
pas de Docker
pas de Node/npm

extraction
composer install
configuration
cache
migrations
smoke tests
activation
```

Structure serveur :

```text
~/sites/<site-rcl>/
├── current -> releases/<version>/public
├── current_release -> releases/<version>
├── releases/
└── shared/
    ├── .env.prod.local
    ├── config/jwt/
    ├── public/.htaccess
    └── var/log/
```

Règle fondamentale :

```text
préparer complètement
        ↓
vérifier
        ↓
migrer
        ↓
activer
```

La release active ne doit pas être modifiée pendant la préparation de la suivante.
