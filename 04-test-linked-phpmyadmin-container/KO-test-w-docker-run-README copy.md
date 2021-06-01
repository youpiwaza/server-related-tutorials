# Add a phpMyAdmin container to an existing project

**Note:** This file was dedicated to tests with docker run, but IDK if it can actually works with the traefik's reverse proxy setup.

---

This folder will regroup tests and notes about using the phpmyadmin image, before been implemented on the server.

## Theory

When an UI SQL is required, boot up a container that allow acces on a specific port/URI.

## Docs

- [phpmyadmin docker image](https://hub.docker.com/_/phpmyadmin)
- [docker run command > expose ports & --link](https://docs.docker.com/engine/reference/run/#expose-incoming-ports)

## Docker run tests

Need a running container/service/stack using mysql/mariadb to link to. I'll use the [wordpress test](ansible-install-web-server/ansible/tmp/tests/masamune/test-wordpress--masamune--fr/test-wordpress--masamune--fr--wordpress-stack--generated.yml), default from *20-setup-a-wordpress.yml* in the *ansible-install-web-server* repo.

All following examples will bring you phpMyAdmin on [localhost:8080](http://localhost:8080) where you can enjoy your happy MySQL administration.

### Premier objectif / Faire tourner le conteneur

Faire tourner un conteneur lancé à la mano et y accéder a l'arrache (~ip:port).

```bash
## Official example
# docker run --name myadmin -d --link mysql_db_server:db -p 8080:80 phpmyadmin

## Décomposée
docker run \
  -d \ # Arrière plan
  --link mysql_db_server:db \ # Add link to another container (<name or id>:alias or <name or id>)
  --name myadmin \ # Name the container
  -p 8080:80 \ # Expose ports
  phpmyadmin # latest image

## KO / Adaptée pour testay
docker run --rm -i -t \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  -p 8080:80 \
  phpmyadmin:fpm-alpine \
  /bin/ash
# docker: No valid trust data for 5.1.0-fpm-alpine.

docker pull phpmyadmin:fpm-alpine
# docker: No valid trust data for 5.1.0-fpm-alpine.

## Problem : bad content trust from phpmyadmin, for ~all recent tags
# Off to a great fucking start

# ~OK / Disable content trust
docker run --rm -i -t \
  --disable-content-trust \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  -p 8080:80 \
  phpmyadmin:fpm-alpine \
  /bin/ash
# docker: Error response from daemon: Cannot link to /test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y, as it does not belong to the default network.

## KO / Using container's network
# --network='container:<name|id>': reuse another container's network stack
docker run --rm -i -t \
  --disable-content-trust \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  --network container:test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  -p 8080:80 \
  phpmyadmin:fpm-alpine \
  /bin/ash
# docker: links are only supported for user-defined networks.

## OK / Using container's public network (traefik)
docker run --rm -i -t \
  --disable-content-trust \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  --network core-traefik-public \
  -p 8080:80 \
  phpmyadmin:fpm-alpine \
  /bin/ash
# >> logged in running container
```

Test sur l'url du serveur : [IP_SERVEUR:8080](http://IP_SERVEUR:8080) >> KO, go reverse proxy

## 2 / Accéder au bousin via une url dédiée

Création d'une url public via le reverse proxy traefik : au moyen de labels dédiés.

### Ajout aux dns

OVH > [oké](http://pma-test-wordpress.masamune.fr/)

### Container config

Besoins:

- ✅ Attaché au réseau traefik
- Labels phpmyadmin, récupération & adaptation sur le [.yml test wp](ansible-install-web-server/ansible/tmp/tests/masamune/test-wordpress--masamune--fr/test-wordpress--masamune--fr--wordpress-stack--generated.yml)
- ✅ Configuré côté phpmyadmin

Doc image : Set the variable PMA_ABSOLUTE_URI to the fully-qualified path (`https://pma.example.net/`) where the reverse proxy makes phpMyAdmin available.

Doc docker run : A label is a key=value pair. To add multiple labels, repeat the label flag (-l or --label).

```bash
## OK / Test label syntax w. docker run
docker run --rm -i -t \
  --disable-content-trust \
  -e PMA_ABSOLUTE_URI='http://pma-test-wordpress.masamune.fr/' \
  --label hey=hoy \
  --label hey2=hoy2 \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  --name test-pma \
  --network core-traefik-public \
  -p 8080:80 \
  phpmyadmin:fpm-alpine \
  /bin/ash
```

#### Convert docker-compose/stack labels to docker run

```yml
### ansible-install-web-server/ansible/tmp/tests/masamune/test-wordpress--masamune--fr/test-wordpress--masamune--fr--wordpress-stack--generated.yml
wordpress:
  deploy:
    labels:
      # Tell Traefik to get the contents provided by this service using that shared network.
      - "traefik.docker.network=core-traefik-public"
      # Allow internet exposition (publish on port 80)
      - "traefik.enable=true"

      ## HTTPS router specifications
      # Entrypoint
      - "traefik.http.routers.https_testWordpress__masamune__fr___wordpress__wordpress__Router.entrypoints=websecure"
      # On which url ? Reverse proxy regexp
      - "traefik.http.routers.https_testWordpress__masamune__fr___wordpress__wordpress__Router.rule=Host(`test-wordpress.masamune.fr`)"
      # Use the service created below specifying the internal port
      - "traefik.http.routers.https_testWordpress__masamune__fr___wordpress__wordpress__Router.service=https_testWordpress__masamune__fr___wordpress__wordpress__Service"
      # Enable TLS
      - "traefik.http.routers.https_testWordpress__masamune__fr___wordpress__wordpress__Router.tls=true"
      # Automtic certifcate resolver, created in traefik.yml
      - "traefik.http.routers.https_testWordpress__masamune__fr___wordpress__wordpress__Router.tls.certresolver=leresolver"
      
      # Create a service specifying the internal port
      # Internal port > 1024 to prevent usage of root restricted ports
      - "traefik.http.services.https_testWordpress__masamune__fr___wordpress__wordpress__Service.loadbalancer.server.port=8080"

      ## HTTP_ router / Redirect every http requests to their https equivalent
      # Create a middleware to redirect http to https
      #     Middleware aren't shared ?
      - "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme=https"
      # All hosts..
      - "traefik.http.routers.http_testWordpress__masamune__fr___wordpress__wordpress__Router.rule=hostregexp(`{host:.+}`)"
      # .. coming from entrypoint web ..
      - "traefik.http.routers.http_testWordpress__masamune__fr___wordpress__wordpress__Router.entrypoints=web"
      # .. use the previously created middleware
      - "traefik.http.routers.http_testWordpress__masamune__fr___wordpress__wordpress__Router.middlewares=redirect-to-https"
      
      - "fr.masamune.test-wordpress.client='masamune'"
      - "fr.masamune.test-wordpress.maintainer='masamune.code@gmail.com'"
      - "fr.masamune.test-wordpress.project='test wordpress / wordpress'"
      - "fr.masamune.test-wordpress.type='test'"

### For precedent docker run
--label "traefik.docker.network=core-traefik-public" \
--label "traefik.enable=true" \

--label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=websecure" \
--label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.rule=Host(`pma-test-wordpress.masamune.fr`)" \
--label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.service=https_pmaTestWordpress__masamune__fr___pma" \
--label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls=true" \
--label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls.certresolver=leresolver" \

--label "traefik.http.services.https_pmaTestWordpress__masamune__fr___pma.loadbalancer.server.port=8080" \

--label "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme=https" \
--label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.rule=hostregexp(`{host:.+}`)" \
--label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=web" \
--label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.middlewares=redirect-to-https" \

--label "fr.masamune.test-wordpress.client='masamune'" \
--label "fr.masamune.test-wordpress.maintainer='masamune.code@gmail.com'" \
--label "fr.masamune.test-wordpress.project='test phpmyadmin / php my admin'" \
--label "fr.masamune.test-wordpress.type='test'" \
```

### Resume to container config

```bash
## OK / Test label syntax w. docker run
docker run --rm -i -t \
  --disable-content-trust \
  -e PMA_ABSOLUTE_URI='http://pma-test-wordpress.masamune.fr/' \
  --label "traefik.docker.network=core-traefik-public" \
  --label "traefik.enable=true" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=websecure" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.rule=Host(`pma-test-wordpress.masamune.fr`)" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.service=https_pmaTestWordpress__masamune__fr___pma" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls=true" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls.certresolver=leresolver" \
  --label "traefik.http.services.https_pmaTestWordpress__masamune__fr___pma.loadbalancer.server.port=8080" \
  --label "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme=https" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.rule=hostregexp(`{host:.+}`)" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=web" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.middlewares=redirect-to-https" \
  --label "fr.masamune.test-wordpress.client='masamune'" \
  --label "fr.masamune.test-wordpress.maintainer='masamune.code@gmail.com'" \
  --label "fr.masamune.test-wordpress.project='test phpmyadmin / php my admin'" \
  --label "fr.masamune.test-wordpress.type='test'" \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  --name test-pma \
  --network core-traefik-public \
  -p 8080:80 \
  phpmyadmin:fpm-alpine \
  /bin/ash
# zsh: command not found: pma-test-wordpress.masamune.fr
# zsh: command not found: host:.+
## `` KO
docker inspect test-pma

# "Labels": {
#     "fr.masamune.test-wordpress.client": "'masamune'",
#     "fr.masamune.test-wordpress.maintainer": "'masamune.code@gmail.com'",
#     "fr.masamune.test-wordpress.project": "'test phpmyadmin / php my admin'",
#     "fr.masamune.test-wordpress.type": "'test'",
#     "org.opencontainers.image.authors": "The phpMyAdmin Team <developers@phpmyadmin.net>",
#     "org.opencontainers.image.description": "Run phpMyAdmin with Alpine, Apache and PHP FPM.",
#     "org.opencontainers.image.documentation": "https://github.com/phpmyadmin/docker#readme",
#     "org.opencontainers.image.licenses": "GPL-2.0-only",
#     "org.opencontainers.image.source": "https://github.com/phpmyadmin/docker.git",
#     "org.opencontainers.image.title": "Official phpMyAdmin Docker image",
#     "org.opencontainers.image.url": "https://github.com/phpmyadmin/docker#readme",
#     "org.opencontainers.image.vendor": "phpMyAdmin",
#     "org.opencontainers.image.version": "5.1.0",
#     "traefik.docker.network": "core-traefik-public",
#     "traefik.enable": "true",
#     "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme": "https",
#     "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.entrypoints": "web",
#     "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.middlewares": "redirect-to-https",

#     "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.rule": "hostregexp()",                                      # KO

#     "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.entrypoints": "websecure",

#     "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.rule": "Host()",                                           # KO

#     "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.service": "https_pmaTestWordpress__masamune__fr___pma",
#     "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls": "true",
#     "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls.certresolver": "leresolver",
#     "traefik.http.services.https_pmaTestWordpress__masamune__fr___pma.loadbalancer.server.port": "8080"
# },


## KO / Replace ` with ' ?
docker run --rm -i -t \
  --disable-content-trust \
  -e PMA_ABSOLUTE_URI='http://pma-test-wordpress.masamune.fr/' \
  --label "traefik.docker.network=core-traefik-public" \
  --label "traefik.enable=true" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=websecure" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.rule=Host('pma-test-wordpress.masamune.fr')" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.service=https_pmaTestWordpress__masamune__fr___pma" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls=true" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls.certresolver=leresolver" \
  --label "traefik.http.services.https_pmaTestWordpress__masamune__fr___pma.loadbalancer.server.port=8080" \
  --label "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme=https" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.rule=hostregexp('{host:.+}')" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=web" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.middlewares=redirect-to-https" \
  --label "fr.masamune.test-wordpress.client='masamune'" \
  --label "fr.masamune.test-wordpress.maintainer='masamune.code@gmail.com'" \
  --label "fr.masamune.test-wordpress.project='test phpmyadmin / php my admin'" \
  --label "fr.masamune.test-wordpress.type='test'" \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  --name test-pma \
  --network core-traefik-public \
  -p 8080:80 \
  phpmyadmin:fpm-alpine \
  /bin/ash
## ~OK ? No complaints in CLI nor docker inspect. DK about traefik

## KO / Don't bind ports
docker run --rm -i -t \
  --disable-content-trust \
  -e PMA_ABSOLUTE_URI='http://pma-test-wordpress.masamune.fr/' \
  --label "traefik.docker.network=core-traefik-public" \
  --label "traefik.enable=true" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=websecure" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.rule=Host('pma-test-wordpress.masamune.fr')" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.service=https_pmaTestWordpress__masamune__fr___pma" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls=true" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls.certresolver=leresolver" \
  --label "traefik.http.services.https_pmaTestWordpress__masamune__fr___pma.loadbalancer.server.port=80" \
  --label "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme=https" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.rule=hostregexp('{host:.+}')" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=web" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.middlewares=redirect-to-https" \
  --label "fr.masamune.test-wordpress.client='masamune'" \
  --label "fr.masamune.test-wordpress.maintainer='masamune.code@gmail.com'" \
  --label "fr.masamune.test-wordpress.project='test phpmyadmin / php my admin'" \
  --label "fr.masamune.test-wordpress.type='test'" \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  --name test-pma \
  --network core-traefik-public \
  -p 80 \
  phpmyadmin:fpm-alpine \
  /bin/ash

## Test access to url from a simple container, to check traefik config
# KO on port 80 and 8080
docker run --rm -i -t \
  --disable-content-trust \
  --label "traefik.docker.network=core-traefik-public" \
  --label "traefik.enable=true" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=websecure" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.rule=Host('pma-test-wordpress.masamune.fr')" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.service=https_pmaTestWordpress__masamune__fr___pma" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls=true" \
  --label "traefik.http.routers.https_pmaTestWordpress__masamune__fr___pma__Router.tls.certresolver=leresolver" \
  --label "traefik.http.services.https_pmaTestWordpress__masamune__fr___pma.loadbalancer.server.port=8080" \
  --label "traefik.http.middlewares.redirect-to-https.redirectscheme.scheme=https" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.rule=hostregexp('{host:.+}')" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.entrypoints=web" \
  --label "traefik.http.routers.http_pmaTestWordpress__masamune__fr___pma__Router.middlewares=redirect-to-https" \
  --label "fr.masamune.test-wordpress.client='masamune'" \
  --label "fr.masamune.test-wordpress.maintainer='masamune.code@gmail.com'" \
  --label "fr.masamune.test-wordpress.project='test phpmyadmin / php my admin'" \
  --label "fr.masamune.test-wordpress.type='test'" \
  --name test-pma \
  --network core-traefik-public \
  -p 8080 \
  tutum/hello-world \
  /bin/ash
```

Don't actually know if it can works through docker run with the traefik setup

I'll stop there & resume with docker-compose
