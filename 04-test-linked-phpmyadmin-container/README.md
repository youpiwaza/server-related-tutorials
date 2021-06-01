# Add a phpMyAdmin container to an existing project

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
  docker.io/library/phpmyadmin:fpm-alpine \
  /bin/ash
# docker: Error response from daemon: Cannot link to /test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y, as it does not belong to the default network.

## KO / Using container's network
# --network='container:<name|id>': reuse another container's network stack
docker run --rm -i -t \
  --disable-content-trust \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  --network container:test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  -p 8080:80 \
  docker.io/library/phpmyadmin:fpm-alpine \
  /bin/ash
# docker: links are only supported for user-defined networks.

## OK / Using container's public network (traefik)
docker run --rm -i -t \
  --disable-content-trust \
  --link test---test-wordpress--masamune--fr_mariadb.1.e1llxb8fl26ecpoxudg5b2r5y \
  --network core-traefik-public \
  -p 8080:80 \
  docker.io/library/phpmyadmin:fpm-alpine \
  /bin/ash
# >> logged in running container
```

Test sur l'url du serveur : [IP_SERVEUR:8080](http://IP_SERVEUR:8080) >> KO, go reverse proxy
