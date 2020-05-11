# Traefik > Enable HTTPS & automated certificate renewals

Now that we have 'simple' a working base on prod environnement, time to enable HTTPS/

## Main commands

```bash
# builder_guy / syslogs globaux
sudo tail -f /var/log/syslog

# docker_guy / Réseau attachable public, pour que les services soient connectés à traefik/internet
docker network create --driver=overlay --attachable traefik-public

# docker_guy / Create a named volume for logs (debug + access)
# Do steps described below in #### How to manage named volumes access rights

# Traefik + proxy
# docker stack deploy -c traefik17.yml traefik # KO stack peut pas privileged
# docker_guy / Lancement via docker compose, sans -d, afin de voir les logs (forcés en json-file)
# docker-compose -f traefik.yml up
# Scaling/replicas
docker-compose -f traefik.yml up --scale dockersocketproxy=2
# docker-compose -f traefik.yml up --scale traefik=2 # KO, as port 80 can be published to one instance only

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

1. 🚀 Ajout https
    1. Docs
       1. [Let's encrypt](https://letsencrypt.org/fr/docs/)
       2. [Traefik > Automated](https://docs.traefik.io/https/acme/)
       3. [Traefik > TLS > Routers](https://docs.traefik.io/routing/routers/#tls)
       4. 💚 [Traefik HTTPS tutorial](https://containo.us/blog/traefik-2-0-docker-101-fc2893944b9d/#i-need-https)
       5. 💚 [Another tutorial](https://chriswiegman.com/2019/10/serving-your-docker-apps-with-https-and-traefik-2/)
