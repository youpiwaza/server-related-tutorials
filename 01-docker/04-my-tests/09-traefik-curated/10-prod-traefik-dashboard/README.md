# KO AF / Traefik > Test traefik auth

- Fuck this shit
- Fuck the documentation
- Fuck the missing instruction almost 1/3 of the time
- Fuck the lack of working examples
- Fuck those guys
- Fuck this waste of time

Mise en place du dashbard traefik (api secure) sur traefik.masamune.fr.

Docs:

- [Dashboard](https://docs.traefik.io/operations/dashboard/)
- [API](https://docs.traefik.io/operations/api/)

## Certificate cache

Note: When all in place, might need to remove test certificates (using Let's encrypt staging uri) from the browser :

- Empty browser cache
- Restart browser

## Main commands

```bash
# builder_guy / syslogs globaux
sudo tail -f /var/log/syslog

# docker_guy / Réseau attachable public, pour que les services soient connectés à traefik/internet
docker network create --driver=overlay --attachable traefik-public

# docker_guy / Create a named volume for logs (debug + access)
# Do steps described in server-related-tutorials/01-docker/04-my-tests/09-traefik-curated/06-prod-traefik-curated/README.md #### How to manage named volumes access rights

# Traefik + proxy
# docker stack deploy -c traefik17.yml traefik # KO stack peut pas privileged
# docker_guy / Lancement via docker compose, sans -d, afin de voir les logs (forcés en json-file)
# docker-compose -f traefik.yml up
# Scaling/replicas
docker-compose -f traefik.yml up --scale dockersocketproxy=2
# docker-compose -f traefik.yml up --scale traefik=2 # KO, as port 80 can be published to one instance only
# Logs traefik
docker exec -it tests_traefik_1 /bin/ash -c "tail -f home/logs/traefik-debug.log"

# docker_guy / Stack de test, sur http://test.masamune.fr/
docker stack deploy -c hello.yml hello
docker stack deploy -c helloDeux.yml helloDeux
docker stack deploy -c helloSub.yml helloSub

# [Online to curl/browser http://test.masamune.fr/](http://test.masamune.fr/)
# [Online to curl/browser http://grafana.masamune.fr/](http://grafana.masamune.fr/)

# docker_guy / Vérifications attributions des containers
docker service ls

# docker_guy / Suppression des services & réseaux
docker-compose -f traefik.yml down
docker stack rm hello
docker stack rm helloDeux
docker stack rm helloSub
docker system prune
```

## TODO

1. Enable traefik insecure api on traefik.masamune.fr
2. Enable traefik secure api
3. Add auth
