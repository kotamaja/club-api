### Création de l'image php
``docker build  . --file ./docker/dev/Dockerfile_php_composer --tag php-composer``

### Exécution du container php
``docker run --rm -it  --user $(id -u):$(id -g) --name php-composer -v ${PWD}:/application php-composer bash``

## Installation du framework symfony
````
composer create-project symfony/skeleton:"8.0.*" symfony

cd symfony

composer require webapp
````

## Mise à jour mineur symfony
````
composer update --dry-run

composer update
````
