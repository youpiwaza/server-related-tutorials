# ~KO / Traefik > HTTPS for domain & all subdomains

Using traefik's tls.domains to enable all subdomains at once

## Need proper DNS

Need main domain DNS to point towards the server. Can't test only on subdomains (test.DOMAIN.com is redundant with *.DOMAIN.com)

> Single sub domain definition for now

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
    2. ✅ Enable in traefik container
    3. ✅ Enable on stack ~[hello https://test.masamune.fr/](https://test.masamune.fr/)
    4. ✅ Automatic redirect http to https
    5. ✅ Switch from Let's encrypt staging (for test purposes)
       1. 🎉 Works
    6. 🌱 Implement 1 [certificate per domain (for all sub domains)](https://docs.traefik.io/https/acme/#domain-definition), cf. SANs (Subject Alternative Name)
       1. Need main DOMAIN.com to point toward the same server
    7. 🌱 Test on helloDeux & HelloSub

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

# Create and set rights to /home/https/
# Traefik needs full access (incl. file creation) on acme.json
#     https://community.containo.us/t/non-existent-resolver-using-letsencrypt/3530
>> chown -R 1003:1003 https/

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

## Resolving traefik cancer doc cryptic bullsh*t

### "router uses a non-existent resolver"

Nothing to do with a link between stack & traefik : traefik needs to CREATE acme.json, so we can't even touch it.
> Resolved by giving traefik full access on the "https" folder created inside the named volume "traefik-https", instead of creating acme.json & then chown it to 1003:1003.

Thanks a lot [somebody](https://community.containo.us/t/non-existent-resolver-using-letsencrypt/3530/2).

### Unable to obtain ACME certificate for domains

Traefik's debug logs

```json
{   "level":"error",
    "msg":"Unable to obtain ACME certificate for domains \"hello-helloworld\":
            unable to generate a certificate for the domains [hello-helloworld]:
            acme: error: 400 :: POST :: https://acme-staging-v02.api.letsencrypt.org/acme/new-order ::
            urn:ietf:params:acme:error:rejectedIdentifier ::
            Error creating new order :: Cannot issue for \"hello-helloworld\":
            Domain name needs at least one dot, url: ",
        "providerName":"leresolver.acme",
        "routerName":"my-testMasamuneFr_Helloworld_Router@docker",
        "rule":"Host(`hello-helloworld`)",
        "time":"2020-05-11T13:15:22Z"
}
```

yeah, Host rule is kinda fucking "test.masamune.fr" and not random "hello-helloworld".
> Router name typo -_-" > Traefik default host rule "stack name" + "-" + "service name"

## Service HTTPS implementation

```yaml
services:
  helloworld:
    deploy:
      labels:
         ## HTTPS router specifications

        # Entrypoint
        - "traefik.http.routers.https_testMasamuneFr_Helloworld_Router.entrypoints=websecure"
        # On which url ? Reverse proxy regexp
        - "traefik.http.routers.https_testMasamuneFr_Helloworld_Router.rule=Host(`test.masamune.fr`)"
        # Use the service created below specifying the internal port
        - "traefik.http.routers.https_testMasamuneFr_Helloworld_Router.service=testMasamuneFr_Helloworld_Service"
        # Enable TLS
        - "traefik.http.routers.https_testMasamuneFr_Helloworld_Router.tls=true"
        # Automtic certifcate resolver, created in traefik.yml
        - "traefik.http.routers.https_testMasamuneFr_Helloworld_Router.tls.certresolver=leresolver"

        # Create a service specifying the internal port
        - "traefik.http.services.testMasamuneFr_Helloworld_Service.loadbalancer.server.port=80".tls.certresolver=leresolver"
```

## Service HTTP redirection

```yaml
services:
  helloworld:
    deploy:
      labels:
        ## HTTP_ router specifications
        #     Redirect every http requests to their https equivalent

        # Create a middleware to redirect http to https
        #     Middleware aren't shared ?
        - "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme=https"
        # All hosts..
        - "traefik.http.routers.http_testMasamuneFr_Helloworld_Router.rule=hostregexp(`{host:.+}`)"
        # .. coming from entrypoint web ..
        - "traefik.http.routers.http_testMasamuneFr_Helloworld_Router.entrypoints=web"
        # .. use the previously created middleware
        - "traefik.http.routers.http_testMasamuneFr_Helloworld_Router.middlewares=redirect-to-https"
```

## 1 certificate for all subdomains, SANs

Docs:

- [certificate per domain (for all sub domains)](https://docs.traefik.io/https/acme/#domain-definition), cf. SANs (Subject Alternative Name)
- [Vraie docs tls.domains](https://docs.traefik.io/routing/routers/#domains)

```yaml
# Doc Example / Multiple Domains from Router's tls.domain Example
deploy:
  labels:
    - traefik.http.routers.blog.rule=Host(`example.com`) && Path(`/blog`)
    - traefik.http.services.blog-svc.loadbalancer.server.port=8080"
    - traefik.http.routers.blog.tls=true
    - traefik.http.routers.blog.tls.certresolver=myresolver
    - traefik.http.routers.blog.tls.domains[0].main=example.org
    - traefik.http.routers.blog.tls.domains[0].sans=*.example.org
```
