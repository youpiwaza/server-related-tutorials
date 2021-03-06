# Curation

Reprise de l'exemple précédent, en utilisant les bonnes pratiques, les recos de sécurité, et les labels.

Recommandations tirées du [repo d'exemple](https://github.com/youpiwaza/docker-compose-curated-example/blob/master/docker-compose.yml).

---

## Commandes de base

```bash
# Autoriser connexion a docker via WLS
> export DOCKER_HOST=tcp://localhost:2375

# Initier docker swarm
> docker swarm init


# Acceder au projet
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/01-docker/04-my-tests/09-traefik-curated/04-traefik-curated

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

## Docker socket proxy healthcheck

[Official image issue](https://github.com/Tecnativa/docker-socket-proxy/issues/24) / KO

```yaml
healthcheck:
  # KO, prevent stack from running
  test: 'curl -fail http://localhost:2375/version || exit 1'
  interval: 10s
  timeout: 10s
  retries: 3
  start_period: 0s
```

## Traefik 2.0 healthcheck

- [discussion](https://community.containo.us/t/how-to-do-healthcheck-on-traefik-itself/1462/6)
- [Official docs](https://docs.traefik.io/v2.0/operations/cli/#healthcheck)
- [Enable ping](https://docs.traefik.io/v2.0/operations/ping/)

```yaml
traefik:
    command:
      # Enable ping
      - "--ping=true"
    healthcheck:
      # Ping da container
      test: 'traefik healthcheck --ping'
      interval: 10s
      timeout: 10s
      retries: 3
      start_period: 0s
```
