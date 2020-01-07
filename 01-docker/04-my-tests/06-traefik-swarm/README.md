# Traefik / Implementation avec docker swarm

.. A partir de la [doc officielle](https://docs.traefik.io/providers/docker/#docker-swarm-mode) et étape par étape

On repart de la configuration de traefik de base ([doc](https://docs.traefik.io/getting-started/configuration-overview/) + exemple 04-traefik)

& Clean des fichiers .yml avec la syntaxe docker actualisée.

Enfin, on suit la [doc](https://docs.traefik.io/providers/docker/)



## Principales commandes

Le but est de monter traefik en tant que service, puis de monter d'autres services et vérifier leur disponibilités sur localhost.

Lancement en tant que service via [swarm](https://docs.docker.com/get-started/part4/)

```bash
# Créer le réseau externe à traefik (lien avec les autres services)
> docker network create --driver=overlay traefik-public

# déploiement de traefik & whoami
> docker stack deploy -c traefik.yml traefik

# Déploiement d'un hello world
> docker stack deploy -c hello.yml hello

# Test avec scale
#> docker service scale SERVICE=REPLICAS
> docker service scale traefik_whoami=3

# Supprimer le réseau
> docker network prune
```

Vérifications sur [http://whoami.localhost/](http://whoami.localhost/) et [http://hello.localhost/](http://hello.localhost/).

- Possibilité de vérifier les réplicas via whoami (changement d'ip lors du rechargement de la page)

Les adresses sont fixées dans les .yml dans `services:LE_SERVICE:deploy:labels` > `- "traefik.http.routers.LE_SERVICE.rule=Host(URL_DU_SERVICE)"`

*Arrêt du service*

```bash
> docker stack rm traefik
> docker stack rm hello
```



## Configuration Dicovery

Doc imbitable, avec l'ensemble écrit pour un fichier de configuration externe, alors que la doc elle même ne le recommande pas (mais plutôt la conf dynamique).

Recherce d'un [tuto](https://creekorful.me/how-to-install-traefik-2-docker-swarm/) afin de voir un minimum la syntaxe -_-. Edit : [doc](https://docs.traefik.io/routing/providers/docker/) // Descendre un peu

**Note concernant la doc docker** / Même si *extrèmement mal branlée à un niveau ou ça en devient du sport*, pour définir les options en dynamique, prendre la configuration au format File (YAML) et remplacer les niveaux par des points ; et mettre dans `command` avec `--` .

EXEMPLE :

```yaml
# Doc swarm mode / https://docs.traefik.io/providers/docker/#swarmmode
providers:
  docker:
    swarmMode: true
    # ...

# Ce qu'il faut faire dans le docker-compose
services:
  traefik:
    command:
      - "--providers.docker.swarmMode=true"
```

Configuration dynamique via labels, options, valeurs & explications


### Traefik

dans **command**

- "--providers.docker.swarmMode=true"
  - [doc](https://docs.traefik.io/providers/docker/#docker-swarm-mode)
  - // Autorise swarm

- "--providers.docker.endpoint=unix:///var/run/docker.sock"
  - [doc](https://docs.traefik.io/providers/docker/#provider-configuration)
  - Default="unix:///var/run/docker.sock"
  - Récupération via le volume (bind ?) qui expose le socket Docker

- "--providers.docker.exposedbydefault=false"
  - [doc](https://docs.traefik.io/providers/docker/#exposedbydefault)
  - Meilleure sécurité, mais besoin de déclarer `traefik.enable=true` dans les conteneurs
    - Conteneurs ou services ?

- "--providers.docker.network=traefik-public"
  - [doc](https://docs.traefik.io/providers/docker/#network)
  - Réseau de connexion à l'ensemble des conteneurs
    - **Note : à voir pour différencier du réseau de connexion externe**

- "--providers.docker.defaultRule"
  - Par défaut > Host(`{{ normalize .Name }}`)

- "--providers.docker.swarmModeRefreshSeconds"
  - Par défaut // 15s / 15 ?

- "--providers.docker.constraints"
  - Par défaut // ""
  - Pas de contraintes, choix des conteneurs via traefik.enabled=true

- tls stuff > plus tard


dans **deploy: placement: constraints**

- - node.role == manager
  - [doc](https://docs.traefik.io/providers/docker/#docker-api-access_1) / [doc docker](https://docs.docker.com/compose/compose-file/#placement)




dans **volumes**

- - /var/run/docker.sock:/var/run/docker.sock
  - [doc](https://docs.traefik.io/providers/docker/#provider-configuration)
  - // The docker-compose file shares the docker sock with the Traefik container



### hellow

*Attention : lors de l'utilisation de swarm avec traefik, les labels doivent être ceux des services (et non des containers), ils sont donc à définir dans deploy*

dans **deploy: labels**

- "traefik.http.services.helloworld.loadbalancer.server.port=80"
  - [doc](https://docs.traefik.io/providers/docker/#port-detection_1)
  - // Docker Swarm does not provide any port detection information to Traefik.
  - // Therefore you **must** specify the port to use for communication
- "traefik.enable=true"
  - [doc](https://docs.traefik.io/providers/docker/#exposedbydefault)
  - Exposer le conteneur au web



## Routing & load balancing

[Doc concepts](https://docs.traefik.io/routing/overview/), puis [Doc pour Docker](https://docs.traefik.io/routing/providers/docker/)

---

Edit : L'envoie de vivre m'a quittée

---

Essai : repartir de [l'exemple de base docker-compose](https://docs.traefik.io/user-guides/docker-compose/basic-example/) et le migrer vers docker swarm en suivant les recos

// WORKS, cf. traefik.yml

Création d'un nouveau (hello).yml afin de lancer un service indépendant et tester la conf.

>> Trop rien de différent, hormis la création du réseau public (swarm préfixe)





TODO :
- ✓ Vérifier bon fonctionnement avec répliques
- Vérifier les conflits de ports, par exemple si je lance un deuxième service hello
- Url par défaut + env surcharge