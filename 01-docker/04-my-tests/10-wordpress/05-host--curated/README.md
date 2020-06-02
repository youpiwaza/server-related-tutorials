# WP curation > Add security recommandations

- ✅ usual stuff, cf. 01-docker/04-my-tests/09-traefik-curated/11-prod-hello-curated/08-hello-stack-curated-comments/hello.yml
- ✅ custom user
- ✅ replicas
- ✅ healthchecks
- Big checkup
  1. ✅ Add article/page
  2. ✅ Upload fichier
  3. ✅ Installation/activation/test plugin

## Notes

### Admin access

- [admin link](https://test-wordpress.masamune.fr/wp-admin/)
- WORDPRESS_USERNAME: WordPress application username. Default: user
- WORDPRESS_PASSWORD: WordPress application password. Default: bitnami

### KO / read-only

Both WP & MariaDb

- containers: KO, need access for tmp/ & regular files
- volumes: KO, same

### KO / Custom user

- bitnami runs by default with non root user "1001"
- has scripts than chown the volumes ont first initialisation
  - Remove and recreate volumes before lunching with new user > KO
  - Inspect new volumes

```bash
# Tmp alpine container to inspect WP/MariaDb volumes
docker run \
    -it \
    --mount \
        source=test-wordpress-datas,target=/bitnami \
        # source=test-wordpress-db,target=/bitnami \
    --rm \
    --workdir /bitnami \
    alpine \
    /bin/ash

# Everything is root chown. Script can't execute i guess
```

Bitnami's Dockerfiles (ex: [maria](https://github.com/bitnami/bitnami-docker-mariadb/blob/10.3.23-debian-10-r18/10.3/debian-10/Dockerfile)) uses a script before switching users

`RUN /opt/bitnami/scripts/mariadb/postunpack.sh`

This [script](https://github.com/bitnami/bitnami-docker-mariadb/blob/10.3.23-debian-10-r18/10.3/debian-10/rootfs/opt/bitnami/scripts/mariadb/postunpack.sh) chmods a lot..

`chmod -R g+rwX "$DB_TMP_DIR" "$DB_LOG_DIR" "$DB_CONF_DIR" "${DB_CONF_DIR}/bitnami" "$DB_VOLUME_DIR" "$DB_DATA_DIR"`

.. before the DF switches back to custom user

`USER 1001`

Works pretty well for now, it's a shame the selected user is 1001 (builder_guy..).

LATER: Make our own Dockerfile with our custom user (~1003).

## Replicas

Services both get up, but website is KO. Nothing in logs, but it might be a port conflict for mariadb as website front errors are mostly 'error connecting DB'.

Test: OK > 2 WP with only one replica of mariadb

Test: ~~OK~~ **KO 1/2** > declare mariadb port without bind + 2 replicas of mariadb

```yaml
## NO, KO 1/2 v
services:
  mariadb:
    deploy:
      mode: replicated
      replicas: 2
    ports:
      - '3306'

# Default mariadb port is 3306
# wordpress:
#   environment:
#     - MARIADB_PORT_NUMBER=3306

# ---

## Bash
docker service ls
# ID                  NAME                            MODE                REPLICAS            IMAGE                      PORTS
# jkhvhfzm860n        test-wordpress_mariadb          replicated          2/2                 bitnami/mariadb:10.3       *:30049->3306/tcp
# 4ysi81fihb5i        test-wordpress_wordpress        replicated          2/2                 bitnami/wordpress:5        *:30047->8080/tcp, *:30048->8443/tcp
```

Edit: **NO. Website still KO 1/2. Reversing to 1 replica for mariadb**.

Edit: Bitnami's MariaDb DH image actually ships with replication recommandations, cf [doc > Setting up a replication cluster](https://hub.docker.com/r/bitnami/mariadb/).

LATER: ^ Doesn't seems so complicated, even has DC examples.

## Healthcheck

Adding healthchecks on both containers. No internet recommandations so we'll cat on config files, following Docs/Docker Bench Security recommandations.

```yaml
services:
  mariadb:
    healthcheck:
      test: 'stat /opt/bitnami/mariadb/conf/my.cnf || exit 1'
      interval: 10s
      timeout: 10s
      retries: 3
      start_period: 0s

  wordpress:
    healthcheck:
      test: 'stat /bitnami/wordpress/wp-config.php || exit 1'
      interval: 10s
      timeout: 10s
      retries: 3
      start_period: 0s
```
