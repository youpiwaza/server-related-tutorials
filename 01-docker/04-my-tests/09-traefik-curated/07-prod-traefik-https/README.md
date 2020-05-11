# Traefik > Enable HTTPS & automated certificate renewals

Now that we have 'simple' a working base on prod environnement, time to enable HTTPS/

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
    2. Enable in traefik container
    3. Enable on stack ~[hello https://test.masamune.fr/](https://test.masamune.fr/)
    4. Automatic redirect http to https

## Traefik HTTPS implementation

### Create a named volume for https certificates

Same stuff as logs.

```bash
# docker_guy
# Create a named volume
docker volume create traefik-https \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='traefik reverse proxy' \
   --label fr.masamune.type='core'

# Shell access through a tmp container
docker run \
   -it \
   --rm \
   --mount \
      source=traefik-https,target=/home/https \
   --workdir /home/https \
   alpine \
   /bin/ash

# Create and set rights to /home/https/acme.json
>> touch acme.json && chown 1003:1003 acme.json

# Vérification
>> ls -la
>> exit
```

### Assign the volume to the traefik container

```yaml
services:
  traefik:
   command:
      # HTTPS certificates
      - --certificatesresolvers.leresolver.acme.storage=/home/https/acme.json
    # Assign the_docker_peon unprivileged user
    # Assign the_docker_peon unprivileged user
    user: 1003:1003
    volumes:
      # Store HTTPS certificates in a named volume
      - type: volume
        read_only: false
        source: traefik-https
        target: /home/https

# Also needs to be defined in the top level volume key
#     https://docs.docker.com/compose/compose-file/#volumes
volumes:
  traefik-https:
    # Use the existing volume, do not recreate one with a prefix
    external: true
```

### Traefik https config

```yaml
services:
  traefik:
   command:
      # HTTPS certificates
      #   Test API TODO: Switch to prod
      - --certificatesresolvers.leresolver.acme.caserver=https://acme-staging-v02.api.letsencrypt.org/directory
      - --certificatesresolvers.leresolver.acme.email=masamune.code@gmail.com
      - --certificatesresolvers.leresolver.acme.storage=/home/https/acme.json
      - --certificatesresolvers.leresolver.acme.tlschallenge=true
      # Specify internet access > HTTP 80 & HTTPS 443
      # Note that "web" & "websecure" are abitrary names, but will be reused in services' contrainers
      - "--entrypoints.web.address=:80"
      - "--entryPoints.websecure.address=:443"
    ports:
      - "80:80"
      - "443:443"
```
