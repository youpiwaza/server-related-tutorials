# Traefik / Routes automatiques

.. A partir de l'[exemple dockerhub](https://hub.docker.com/_/traefik)

## Principales commandes

Le but est de monter traefik en tant que service, puis de monter d'autres services et vérifier leur disponibilités sur localhost **mais sans avoir définit leurs routes via les labels**.

Lancement en tant que conteneur via docker-compose.

```bash
# Lancement sans swarm en arrière plan
> docker-compose -f traefik.yml up -d

# Création de containers alakon
> docker-compose -f who.yml up -d
> docker-compose -f hello.yml up -d
```

Vérifications sur [http://whoami.localhost/](http://whoami.localhost/) et [http://helloworld3.localhost/](http://helloworld3.localhost/).

- Possibilité de vérifier les réplicas via whoami (changement d'ip lors du rechargement de la page)

Les adresses sont fixées dans les .yml dans `--providers.docker.defaultRule` et des shenanigans, à partir des noms de services fixés dans les .yml des services respectifs.

Arrêt des containers

```bash
> docker-compose -f who.yml down
> docker-compose -f hello.yml down
```

## Mise en place de l'exemple ~~en mode no brain~~

En l'état ça ne marche pas (mode swarm ?). Histoire de ne pas écrire pour ne rien dire, le résumé : En passant par docker-compose (**pour les deux**) ça tourne :

Edit : utiliser ```--providers.docker.defaultRule="Host(`{{ normalize .Name }}.docker.localhost`)"``` dans traefik.yml pour vérifier cet exemple.

```bash
# Lancement sans swarm en arrière plan
> docker-compose -f traefik.yml up -d

# Création de containers alakon
> docker-compose -f who.yml up -d
```

[http://who-07-traefik-routes-auto.docker.localhost/](http://who-07-traefik-routes-auto.docker.localhost/) // ok !

### Note

Traefik via compose & whoami via docker run :

Le service apparait bien dans traefik **ET sa route est correctement configurée automatiquement** > ```Host(`test.docker.localhost`)```, par contre impossible d'y accéder.

Testé avec ouverture des ports et ajout au réseau traefik > KO

## Test avec un nom personnalisé

Attention, container_name ne fonctionne pas si déploiement via swarm

[container_name: my-web-container](https://docs.docker.com/compose/compose-file/#container_name)

```bash
> # Lancement de deux containers whoami & hello
> docker container ls # names ok
```

Accès via nom KO, UI traefik :

RULE
Host(`who-07-traefik-routes-auto.docker.localhost`) // OK
NAME
who-07-traefik-routes-auto@docker

Analyse de la [doc](https://docs.traefik.io/providers/docker/#defaultrule) > Syntaxe Go & Sprig

Simple : 5 premiers caractères : ```--providers.docker.defaultRule="Host(`{{ substr 0 5 .Name }}.docker.localhost`)"```

Avancé : Virer tout ce qu'il y a après le premier '-' // Dur de choper le string index en go/spring !?

Doc traefik cancer > ```defaultRule: "Host(`{{ .Name }}.{{ index .Labels \"customLabel\"}}`)"```

```yml
--providers.docker.defaultRule="Host(`{{ .Name }}.{{ index .Labels \"customLabel\" }}`)"

# Sur UI, ajoute un . (le séparateur)
who-07-traefik-routes-auto..docker.localhost
```

Test empirique

```yml
--providers.docker.defaultRule="Host(`{{ .Name }}.{{ index .Labels" }}`)"

# Sur UI
map[
    com.docker.compose.config-hash:3554a3bbabc2554aec33f2e32e4bd500b1ad15f7f39634d6715a3058371526d0
    com.docker.compose.container-number:1
    com.docker.compose.oneoff:False
    com.docker.compose.project:07-traefik-routes-auto
    com.docker.compose.project.config_files:who.yml
    com.docker.compose.project.working_dir:/c/Users/Patolash/Documents/_dev/server-related-tutorials/01-docker/04-my-tests/07-traefik-routes-auto
    com.docker.compose.service:who
    com.docker.compose.version:1.25.1-rc1
]
# yay
```

*100k années plus tard* - Je comprend vraiment pas quel langage c'est..

```yml
# index MAP "NOM_CASE" > wat. the. fuck.
--providers.docker.defaultRule="Host(`{{ .Name }}.{{ index .Labels \"com.docker.compose.service\"}}`)"

# UI > RULE > Host(`who-07-traefik-routes-auto.who`)

--providers.docker.defaultRule="Host(`{{ index .Labels \"com.docker.compose.service\"}}.localhost`)"
# Host(`who.localhost`)
# Host(`helloworld.localhost`)
```

Test [http://who.localhost/](http://who.localhost/).

Bon ça chie sur le nom du container, mais c'est ptet pas plus mal

- Plus besoin de nommer les containers
- Adresse locale en fonction du nom du service

## Test avec swarm

Peu d'intérêt vu qu'en théorie on a aucune raison de faire tourner swarm en local pour développer. Mais test quand même #curieux

```bash
> docker stack deploy -c hello.yml yay
# NAME > 07-traefik-routes-auto_helloworld3_1
# Traefik UI > Host(`.localhost`)
```

yay

```yaml
--providers.docker.defaultRule="Host(`{{ .Labels }}`)"

# Traefik UI > service Host
Host(`
    map[com.docker.stack.namespace:yay
        com.docker.swarm.node.id:uc1ugbnrpdrskva7gdursmfip
        com.docker.swarm.service.id:aoojcx4brf6agsndaxk5h0x2x
        com.docker.swarm.service.name:yay_helloworld3
        com.docker.swarm.task:
        com.docker.swarm.task.id:wfkg546wzx9405b1wis7qr8wf
        com.docker.swarm.task.name:yay_helloworld3.1.wfkg546wzx9405b1wis7qr8wf
    ]
`)

# Adapter au cazou, pouré éviter les doublons de route
--providers.docker.defaultRule="Host(`{{ index .Labels \"com.docker.compose.service\"}}{{ index .Labels \"com.docker.swarm.service.name\"}}.localhost`)"

# Traefik UI > Ok
# Test sur localhost > Bad Gateway
```

Note : **Attention com.docker. compose/swarm**

## Conteneur indépendant, round 2

```bash
> docker run -d -P --name iamfoo containous/whoami
```

Host KO

```yaml
--providers.docker.defaultRule="Host(`{{ .Labels }}`)"

# Traefik UI > service Host
Host(`map[]`)
# Lel

--providers.docker.defaultRule="Host(`{{ .Name }}`)"

# Traefik UI > service Host
Host(`iamfoo`)
```

- [doc sprig empty](https://masterminds.github.io/sprig/defaults.html#empty)
- [doc sprig ternary](https://masterminds.github.io/sprig/defaults.html#ternary)

```yml
# Si .Labels est vide (compose ou swarm), afficher le .Name, sinon afficher le nom du service
--providers.docker.defaultRule="Host(`{{ empty .Labels | ternary .Name ( index .Labels \"com.docker.compose.service\" ) }}.localhost`)"

# Gestion des cas : swarm > compose > run
--providers.docker.defaultRule="Host(`{{
    # Si swarm n'est pas défini
    empty ( index .Labels \"com.docker.swarm.service.name\" ) | ternary
        # Si compose n'est pas défini
        ( empty ( index .Labels \"com.docker.compose.service\" ) | ternary
            # (run) On utilise le nom
            .Name
            # Sinon (compose) On utilise le service
            (index .Labels \"com.docker.compose.service\")
        )
        # Sinon (swarm) On utilise le nom du service
        (index .Labels \"com.docker.swarm.service.name\")
}}.localhost`)"
```

Ca roule bieng

![Démo routes auto](/01-docker/04-my-tests/07-traefik-routes-auto/docs/images/200108-traefik-routes-auto.jpg)

Mais pour **run & swarm toujours bad gateway**, et un peu flemme :}

## Note concernant consul

Je vois vraiment pas l'intérêt :3

Okay c'est dynamique avec clés/valeurs may je préfère définir l'url dans le projet direct..

Mise de côté pour le moment.
