# Curation

Reprise de l'exemple précédent, en corrigeant pour s'approcher du produit final

---

## Commandes de base

```bash
# Autoriser connexion a docker via WLS
> export DOCKER_HOST=tcp://localhost:2375

# Initier docker swarm
> docker swarm init


# Acceder au projet
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/01-docker/04-my-tests/09-traefik-curated/02-linted-traefik-swarm

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

- Monter une stack Traefik
- Monter une stack hello world (répliqué 3 fois) sur [http://hello.localhost/](http://hello.localhost/) spécifiée en label, utilisant le load balancing
- Monter une stack hello world 2 (sans réplique) sur [http://hello2.localhost/](http://hello2.localhost/) spécifiée en label
- Monter une stack hello world 3 (sans réplique) sur un sous dossier avec [http://hello.localhost/sub/](http://hello.localhost/sub/) spécifiée en label

### Configuration de Traefik nécessaire pour swarm

Required:

- Set the [swarmMode directive to true](https://docs.traefik.io/providers/docker/#docker-swarm-mode)
- Autoriser l'accès la [socket de docker](https://docs.traefik.io/providers/docker/#provider-configuration)
- Besoin d'un [reseau overlay dédié](https://docs.traefik.io/providers/docker/#network)
- Spécifier les [entrypoints](https://docs.traefik.io/v2.2/routing/entrypoints/) de Traefik (Ports/Headers sur lesquels écouter)
  - Port 80 / HTTP
  - Port 443 / HTTPS
- Traefik doit tourner sur les [managers uniquement](https://docs.traefik.io/providers/docker/#docker-api-access_1) (les seuls qui exposent l'API Docker)

Recos:

- Seulement les [conteneurs souhaités](https://docs.traefik.io/providers/docker/#exposedbydefault)
- Defines a default [docker network](https://docs.traefik.io/providers/docker/#network) to use for connections to all containers

```bash
> docker network create --driver=overlay traefik-public
```

```yml
# traefik.yml
services:
  traefik:
      command:
        # Ports sur lesquels Traefik écoute ; HTTP & HTTPS
        - "--entrypoints.web.address=:80"
        # Redirect http towards https
        # TODO: test
        # - "--entrypoints.web.http.redirections.entryPoint.to=websecure"
        # - "--entrypoints.web.http.redirections.entryPoint.scheme=https"
        - "--entryPoints.websecure.address=:443"
        # Provider : docker swarm
        - "--providers.docker.swarmMode=true"
        # Uniquement les conteneurs souhaités
        - "--providers.docker.exposedbydefault=false"
        # Defines a default docker network to use for connections to all containers
        - "--providers.docker.network=traefik-public"
      deploy:
        placement:
          # Deploy on manager nodes only
          constraints:
            - node.role == manager
      image: "traefik:v2.1.1"
      networks:
      - traefik-public
      ports:
      # Publication sur le port 80
      - "80:80"
      volumes:
      # Connexion à la socket docker, afin d'espionner les montages/démontages de conteneurs
      - "/var/run/docker.sock:/var/run/docker.sock:ro"

# Nécéssaire si lancement du service depuis un autre fichier .yml (swarm préfixe le nom du réseau)
# Exemple avec hello.yml
networks:
  traefik-public:
    external: true
```

### Configuration des services déployés

Required:

- Besoin d'un [reseau overlay dédié](https://docs.traefik.io/providers/docker/#network)
- Besoin de [spécifier le port PRIVÉ](https://docs.traefik.io/providers/docker/#port-detection_1) (obligatoire avec swarm)
- Besoin d'[exposer le conteneur](https://docs.traefik.io/providers/docker/#exposedbydefault)
- Besoin de [spécifier la route](https://docs.traefik.io/routing/providers/docker/#routers) + [doc routes](https://docs.traefik.io/routing/routers/#rule)

Recos:

- [Définition explicite du réseau](https://docs.traefik.io/v2.2/routing/providers/docker/#traefikdockernetwork) traefik

```yml
services:
  helloworld:
    deploy:
      labels:
        # Définition explicite du réseau de traefik
        - "traefik.docker.network=traefik-public"
        # Uniquement les conteneurs souhaités
        - "traefik.enable=true"

        # Url à laquelle est associé le service, ici http://hello.localhost/
        - "traefik.http.routers.helloworld.rule=Host(`hello.localhost`)"

        # Obligation de spécifier le port avec docker swarm ; hello world publie sur le port 80 par défaut
        - "traefik.http.services.helloworld.loadbalancer.server.port=80"
    image: tutum/hello-world:latest
    networks:
      - traefik-public
      replicas: 3
  
  helloworld3:
    deploy:
      labels:
        - "traefik.docker.network=traefik-public"
        - "traefik.enable=true"
        # Test container on dedicated sub folders
        # http://hello.localhost/sub/
        - "traefik.http.routers.helloworld3.rule=(Host(`hello.localhost`) && PathPrefix(`/sub`))"
        # Strip prefix to allow correct assets path
        #   https://docs.traefik.io/v2.0/middlewares/overview/
        #   https://docs.traefik.io/v2.0/middlewares/stripprefix/
        # Create a middleware named 'helloworld3pathstrip' that strips the prefix '/sub', added in routers.helloworld3.rule ^
        - "traefik.http.middlewares.helloworld3pathstrip.stripprefix.prefixes=/sub"
        # Apply the middleware 'helloworld3pathstrip' to routers.helloworld3
        - "traefik.http.routers.helloworld3.middlewares=helloworld3pathstrip@docker"
        - "traefik.http.services.helloworld3.loadbalancer.server.port=80"
    image: tutum/hello-world:latest
    networks:
      - traefik-public

networks:
  traefik-public:
    external: true
```

## Later

Traefik check for unhealthy servers / [Add healthcheck](https://docs.traefik.io/v2.2/routing/services/#health-check)

> Configuration in services deploy labels : [here](https://docs.traefik.io/v2.2/routing/providers/docker/#services)
