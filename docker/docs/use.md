# Utilisation de l'api

## Authentification de l'utilisateur à l'api

### Création du token JWT

```shell
curl -k -i -X POST https://localhost/api/auth/login \
-H "Content-Type: application/json" \
-d '{"email":"test@example.ch","password":"ton-mot-de-passe","refreshTokenMode":"none"}'
```

email: email de la l'utilisateur de connection 
password: mot de passe de l'utilisateur de connection
refreshTokenMode: "body", "cookie" ou "none"


La valeur de refreshTokenMode définit la manière dont le refresh token sera retourné

* body (valeur par défaut). Le refresh token est retourné dans le body de la réponse

Exemple :

Body Request : {"email":"test@test.com","password":"xxx", "refreshTokenMode":"body"}

Body Response : {"token": "eyJ0eXAi...Up-kX5OA","refreshToken": "P0HV4x...MDld2Q"}

Le mode body est utilisé dans le cadre d'un application lourde qui nécessite de connaitre le refresh token.

* cookie. Le refresh token est retourné dans un cookie

Exemple :

Body Request : Body Request : {"email":"test@test.com","password":"xxx", "refreshTokenMode":"body"}

Body Reponse : {"token": "eyJ0eX...HMYfGe5x5Q"}

Le mode cookie est utilisé dans le cadre d'un frontend application web.

* none. Aucun refresh token n'est généré

Body Request : Body Request : {"email":"test@test.com","password":"xxx", "refreshTokenMode":"none"}

Body Reponse : {"token": "eyJ0eX...HMYfGe5x5Q"}


### Utilisation du JWT

Une fois le JWT acquis, il faut le transmettre à chaque requête sur l'api afin que l'api reconnaisse l'utilisateur connecté

La tranmission du JWT s'effectue dans un header de la requête.


```shell
curl -k -i -X GET/POST/PATCH/DELETE https://localhost/api/xxx \
-H "Content-Type: application/json" \
-H "Authorization: Bearer JWT" \
-d '{"xxx":"yyy"}'
```



###  Renouvellement du JWT

Pour des raisons de sécurité, la durée de vie du JWT est courte. Une fois ce délai passé, il faut le renouveller.

Requête
```shell
curl -k -i -X POST https://localhost/api/auth/refresh \
-H "Content-Type: application/json" \
-d '{"refreshToken":"kJcCLkXwb...Q3pWMg"}'
```
Réponse
```json
{
    "token": "eyJ0eXAiO...3V6R4VbA",
    "refreshToken": "sI5ke0dly...XoQnQw"
}
```

token contient le nouveau JWT. refreshToken contient le nouveau refreshToken à utiliser lors du prochaine renouvellement de JWT.
La valeur du refreshToken apparait dans le body de la réponse uniquement avec le refreshTokenMode à body.

### Lecture de l'utilisateur connecté

Le endpoint /me est utilisé pour lire des informations de l'utilisateur connecté.

Requête
```shell
curl -k -i -X GET https://localhost/api/me \
-H "Content-Type: application/json" \
-H "Authorization: Bearer JWT" \
```
Exemple de réponse
```json
{
  "id": "01KTEPS2Y4JC3BKK229PYSQCJM",
  "email": "test@example.ch",
  "status": "active",
  "roles": [
    "ROLE_USER"
  ]
}
```

### Lecture des organisations disponibles pour l'utilisateur connecté

Requête
```shell
curl -k -i -X GET https://localhost/api/me/organizations \
-H "Content-Type: application/json" \
-H "Authorization: Bearer JWT" \
```

Exemple de réponse
```json

[{
  "organizationUserId": "01KTEPS3R6YJH96AVWYC9K93G1",
  "organizationId": "01KTEPS2H9C8RMHPH3XG8CHZHA",
  "organizationName": "Association Jurassienne Aviron",
  "organizationSlug": "association-jurassienne-aviron",
  "roles": [],
  "enabled": true,
  "person": {
    "id": "01KTEPS3958SVJMQ0RDYWX029J",
    "firstname": "yves",
    "lastname": "b",
    "email": "yves.b@test.com"
  }
},
{
  "organizationUserId": "01KTEPS3R5Z52BPZ41Y5ENY3NJ",
  "organizationId": "01KTEPS2H9C8RMHPH3XG8CHZH9",
  "organizationName": "Association Vaudoise Aviron",
  "organizationSlug": "association-vaudoise-aviron",
  "roles": [],
  "enabled": true,
  "person": {
    "id": "01KTEPS3958SVJMQ0RDYWX029C",
    "firstname": "yves",
    "lastname": "a",
    "email": "yves.a@test.com"
  }
}]
```
Ceci nous informe que l'utilisateur connecté a l'accès à deux organisations (01KTEPS2H9C8RMHPH3XG8CHZHA et 01KTEPS2H9C8RMHPH3XG8CHZH9) 
ainsi que pour chacune d'entre elles à une personne précise (01KTEPS3958SVJMQ0RDYWX029J et 01KTEPS3958SVJMQ0RDYWX029C)

L'application client doit maintenant choisir avec quelle organisation elle utilise l'api. Seule une organisation peut être 
utilisée à la fois.

Les endpoints de l'api (autre que /me et /me/organizations) s'attendent à recevoir l'id de l'organisation dans le header de 
la requête. L'api retourne une erreur si cette valeur n'est pas fournie.

### Sélection de l'organisation dans les endpoints de l'api

Comme dit précédemment, l'id de l'organisation soit être passée à chaque requête (sauf pour /me et /me/organizations) de cette manière

Exemple de requête
```shell
curl -k -i -X GET https://localhost/api/v1/clubs/01KTEPS3891FMQEG4374ZC7XMN \
-H "Content-Type: application/json" \
-H "Authorization: Bearer JWT" \
-H "X-Organization-Id: 01KTEPS2H9C8RMHPH3XG8CHZH9" \
```

Exemple de réponse
```json
{
    "id": "01KTEPS3891FMQEG4374ZC7XMN",
    "name": "Rowing Club Lausanne"
}
``