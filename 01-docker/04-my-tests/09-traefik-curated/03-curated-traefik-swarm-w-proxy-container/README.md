# Curation

Reprise de l'exemple précédent, en utilisant un [conteneur proxy](https://chriswiegman.com/2019/11/protecting-your-docker-socket-with-traefik-2/) pour l'accès à la socket de docker.

Docs:

- [Docker socket proxy image](https://github.com/Tecnativa/docker-socket-proxy)
- [+1 authorize access](https://github.com/Tecnativa/docker-socket-proxy#not-always-needed)
- [+2 syntax](https://github.com/containous/traefik/issues/4174#issuecomment-439944216)
  - Après tests, autoriser SERVICES NETWORKS TASKS via l'environnement de l'image tecnativa/docker-socket-proxy

---

## Commandes de base

```bash
# Autoriser connexion a docker via WLS
> export DOCKER_HOST=tcp://localhost:2375

# Initier docker swarm
> docker swarm init


# Acceder au projet
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/01-docker/04-my-tests/09-traefik-curated/03-curated-traefik-swarm-w-proxy-container

# Créer un réseau overlay public, différent de ingress (defaut docker)
> docker network create --driver=overlay traefik-public

# Déploiement de traefik & des deux hello world
> docker stack deploy -c traefik.yml traefik && docker stack deploy -c hello.yml hello && docker stack deploy -c hello2.yml hello2
# Tests & admin
# http://hello.localhost/       // hello world 1, load balanced between 3 replicas
# http://hello2.localhost/      // hello world 2
# http://hello.localhost/sub/   // subfolder redirect on service hello world 3
# http://localhost/             // 404
# http://localhost:8080/        // Traefik backend

# Suppression de l'exemple
> docker stack rm traefik && docker stack rm hello && docker stack rm hello2 && docker network rm traefik-public

```

## Exemple curated

Implémentation d'une base Traefik avec Docker swarm :

- Monter une stack Traefik (répliqué 2 fois)
- Monter une stack conteneur proxy, pour accès a la socket docker, avec accès exclusif aux stacks Traefik
- Monter une stack hello world (répliqué 3 fois) sur [http://hello.localhost/](http://hello.localhost/) spécifiée en label, utilisant le load balancing
- Monter une stack hello world 2 (sans réplique) sur [http://hello2.localhost/](http://hello2.localhost/) spécifiée en label
- Monter une stack hello world 3 (sans réplique) sur un sous dossier avec [http://hello.localhost/sub/](http://hello.localhost/sub/) spécifiée en label

### Mise en place du conteneur proxy

Utilisation de [l'image dédiée](https://github.com/Tecnativa/docker-socket-proxy).

Donner autorisations pour networks, services & tasks (tests empiriques).

On donne l'accès a la socket docker, en read-only, et on la publie sur le port usuel (2375), sur un réseau dédié.

```yml
# traefik.yml
services:
  dockersocketproxy:
    deploy:
      placement:
        constraints:
          - node.role == manager
    environment:
      # Authorize access
      NETWORKS: 1
      SERVICES: 1
      TASKS: 1
    image: tecnativa/docker-socket-proxy
    networks:
      - dockersocket4traefiknet
    ports:
      # Publish on usual port
      - 2375
    volumes:
      # Access to socket, read-only for security
      - "/var/run/docker.sock:/var/run/docker.sock:ro"
```

## Configuration de Traefik

- Retrait de l'accès direct à la socket docker.
- Changement du provider
- Dépendance au conteneur proxy
- Création d'un réseau dédié à l'accès socket, et ajout des stacks Traefik
  - Sécurité : Pas d'accès de la part d'autres containers

```yml
services:
  # Cf. ci-dessus ^
  # dockersocketproxy:
  
  traefik:
    command:
      - "--entrypoints.web.address=:80"
      - "--entryPoints.websecure.address=:443"

      # Traefik socket access from the proxy container, on the defined port
      - "--providers.docker.endpoint=tcp://dockersocketproxy:2375"

      - "--providers.docker.exposedbydefault=false"
      - "--providers.docker.swarmMode=true"
    depends_on:
      - dockersocketproxy
    deploy:
      placement:
        constraints:
          - node.role == manager
      replicas: 2
    # Traefik 2.2
    image: "traefik:chevrotin"
    networks:
      # Traefik's docker socket access on dedicated network
      - dockersocket4traefiknet
      # Traefik access to web server containers, to allow reverse proxy serving
      - traefik-public
    ports:
      - "80:80"
      # - "443:443"
    # Remove direct access to docker socket
    # volumes:
    #   - "/var/run/docker.sock:/var/run/docker.sock"

networks:
  # Create dedicated network
  dockersocket4traefiknet:
    # Prevent access to this network from other containers
    attachable: false

  traefik-public:
    external: true
```

## Later

Traefik check for unhealthy servers / [Add healthcheck](https://docs.traefik.io/v2.2/routing/services/#health-check)

> Configuration in services deploy labels : [here](https://docs.traefik.io/v2.2/routing/providers/docker/#services)
