# Traefik / Implementation avec docker swarm

Reprise de server-related-tutorials/01-docker/04-my-tests/06-traefik-swarm, cf. ce projet pour plus de doc

Pas touché au code de l'exemple précédent, juste cleané la doc

---

## Commandes de base

```bash
# Autoriser connexion a docker via WLS
> export DOCKER_HOST=tcp://localhost:2375

# Initier docker swarm
> docker swarm init


# Acceder au projet
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/01-docker/04-my-tests/09-traefik-curated/01-basic-traefik-swarm

# Créer un réseau overlay public, différent de ingress (defaut docker)
> docker network create --driver=overlay traefik-public

# Déploiement de traefik & des deux hello world
> docker stack deploy -c traefik.yml traefik && docker stack deploy -c hello.yml hello && docker stack deploy -c hello2.yml hello2
# Tests & admin
# http://hello.localhost/   // hello world 1, load balanced between 3 replicas
# http://hello2.localhost/  // hello world 2
# http://localhost/         // 404
# http://localhost:8080/    // Traefik backend

# Suppression de l'exemple
> docker stack rm traefik && docker stack rm hello && docker stack rm hello2 && docker network rm traefik-public

```

## Exemple de base

Implémentation d'une base Traefik avec Docker swarm :

- Monter une stack Traefik
- Monter une stack hello world (répliqué 3 fois) avec [adresse](http://hello.localhost/) spécifiée en label
- Monter une stack hello world 2 (sans réplique) avec [adresse](http://hello2.localhost/) spécifiée en label

### Configuration de Traefik nécessaire pour swarm

- Set the [swarmMode directive to true](https://docs.traefik.io/providers/docker/#docker-swarm-mode)
- Autoriser l'accès la [socket de docker](https://docs.traefik.io/providers/docker/#provider-configuration)
- Seulement les [conteneurs souhaités](https://docs.traefik.io/providers/docker/#exposedbydefault)
- Besoin d'un [reseau overlay dédié](https://docs.traefik.io/providers/docker/#network)
- Spécifier les [entrypoints](https://docs.traefik.io/v2.2/routing/entrypoints/) de Traefik (Ports/Headers sur lesquels écouter)

```bash
> docker network create --driver=overlay traefik-public
```

```yml
# traefik.yml
services:
  traefik:
      image: "traefik:v2.1.1"
      command:
        # Affichage du backend Traefik
        - "--api.insecure=true"
        # Ports sur lesquels Traefik écoute
        - "--entrypoints.web.address=:80"
        # Provider : docker swarm
        - "--providers.docker.swarmMode=true"
        # Uniquement les conteneurs souhaités
        - "--providers.docker.exposedbydefault=false"
      networks:
      - traefik-public
      ports:
      # Publication sur le port 80
      - "80:80"
      # Affichage du backend Traefik
      - "8080:8080"
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

- Besoin d'un [reseau overlay dédié](https://docs.traefik.io/providers/docker/#network)
- Besoin de [spécifier le port PRIVÉ](https://docs.traefik.io/providers/docker/#port-detection_1) (obligatoire avec swarm)
- Besoin d'[exposer le conteneur](https://docs.traefik.io/providers/docker/#exposedbydefault)
- Besoin de [spécifier la route](https://docs.traefik.io/routing/providers/docker/#routers) + [doc routes](https://docs.traefik.io/routing/routers/#rule)

```yml
services:
  helloworld:
    image: tutum/hello-world:latest
    networks:
      - traefik-public
    deploy:
      labels:
        # Uniquement les conteneurs souhaités
        - "traefik.enable=true"
        # Url à laquelle est associé le service, ici http://hello.localhost/
        - "traefik.http.routers.helloworld.rule=Host(`hello.localhost`)"
        # Obligatiion de spécifier le port avec docker swarm ; hello world publie sur le port 80 par défaut
        - "traefik.http.services.helloworld.loadbalancer.server.port=80"
      replicas: 3

networks:
  traefik-public:
    external: true
```
