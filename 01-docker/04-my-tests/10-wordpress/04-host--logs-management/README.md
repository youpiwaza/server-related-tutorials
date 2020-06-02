# Gestion des logs

Cleaner la gestion des logs:

1. Apache
2. Mariadb
3. Wordpress
4. PHP
5. Containers

Even when removing forced json-file type, errors aren't logged to syslog.

Quick search brings up several errormanagemetn documentations:

- [Global documentation](https://docs.bitnami.com/oci/apps/wordpress/get-started/first-steps/), then in troobleshooting
- Note that docs may differ regarding provider (azure/aws/etc.) and no reference is made to know which doc to look at w. the docker image..
  - Not OCI (uses Mysql)
  - Going with BCH > Bitnami Cloud Hosting
- [Debug WordPress Errors](https://docs.bitnami.com/bch/apps/wordpress/troubleshooting/debug-errors/)
- [Debug Apache Errors](https://docs.bitnami.com/bch/apps/wordpress/troubleshooting/debug-errors-apache/)
- ~~[Check The MySQL Log File](https://docs.bitnami.com/bch/apps/wordpress/troubleshooting/debug-errors-mysql/)~~
- ~~[Debug PHP-FPM Errors](https://docs.bitnami.com/bch/apps/wordpress/troubleshooting/debug-errors-php-fpm/)~~
- ~~[Debug PhpMyAdmin Errors](https://docs.bitnami.com/bch/apps/wordpress/troubleshooting/debug-errors-phpmyadmin/)~~
- ~[Check The MariaDB Log Files](https://docs.bitnami.com/aws/apps/codedx/troubleshooting/debug-errors-mariadb/)
- ~~[WP general Troobleshoots](https://docs.bitnami.com/general/how-to/troubleshoot-wordpress-issues/)~~

Check the relevant documentation > Quick analyze of the [dockerfile](https://github.com/bitnami/bitnami-docker-wordpress/blob/5.4.1-debian-10-r31/5/debian-10/Dockerfile).

- apache-2.4.43-1
- php-7.4.6-1
- wordpress-5.4.1-8
- image de mariadb:10.3

## Explore services content

```bash
## No alpine, use bash
# wp
docker exec -it WP_CONTAINER_NAME bash

# maria
docker exec -it MARIADB_CONTAINER_NAME bash
```

## Apache

cf. [doc](https://docs.bitnami.com/oci/apps/wordpress/get-started/understand-config/).

**Configuration** > WP container > /opt/bitnami/apache2/conf/httpd.conf

**Logs** > Wp container > /opt/bitnami/apache2/logs/

- error_log
- access_log

```bash
# One liners
docker exec -it WP_CONTAINER_NAME bash -c 'cat /opt/bitnami/apache2/conf/httpd.conf'
docker exec -it WP_CONTAINER_NAME bash -c 'tail -f /opt/bitnami/apache2/logs/error_log'
docker exec -it WP_CONTAINER_NAME bash -c 'tail -f /opt/bitnami/apache2/logs/access_log'
```

## PHP

cf. [doc](https://docs.bitnami.com/oci/apps/wordpress/get-started/understand-default-config-php/).

**Configuration** > WP container > /opt/bitnami/php/etc/php.ini

**Logs** > /opt/bitnami/php/logs ? #TODO: Confirm

```bash
# One liners
docker exec -it WP_CONTAINER_NAME bash -c 'cat /opt/bitnami/php/etc/php.ini'
# docker exec -it WP_CONTAINER_NAME bash -c 'tail -f /opt/bitnami/php/logs' # TODO: Adjust file, /logs is a folder
```

## Database

No mysql conf in WP container ( > /opt/bitnami/mysql/my.cnf ).

cf. ~~[doc](https://docs.bitnami.com/aws/apps/codedx/troubleshooting/debug-errors-mariadb/)~~.

Wrong path in doc !

**Configuration** > MariaDb container > /opt/bitnami/mariadb/conf/my.cnf

Note: Don't override file, use /opt/bitnami/mariadb/conf/my_custom.cnf, automatically loaded, cf. [doc > configuration file](https://hub.docker.com/r/bitnami/mariadb/).

**Logs** > MariaDb container > /opt/bitnami/mariadb/logs/mysqld.log

- Also linked to /dev/stdout

```bash
# One liners
docker exec -it MARIADB_CONTAINER_NAME bash -c 'cat /opt/bitnami/mariadb/conf/my.cnf'
docker exec -it MARIADB_CONTAINER_NAME bash -c 'tail -f /opt/bitnami/mariadb/logs/mysqld.log'
```

## WordPress

**Configuration** > WP container >

- original:   /opt/bitnami/wordpress
  - Full installation
- linked to:  /bitnami/wordpress/
  - Only wp-config.php & wp-content are linked

No logs specified in the [doc](https://docs.bitnami.com/bch/apps/wordpress/troubleshooting/debug-errors/).

```bash
# One liners
docker exec -it WP_CONTAINER_NAME bash -c 'cat /bitnami/wordpress/wp-config.php'
```

## Containers logs

Find a way to get containers logs (docker-compose without -d & json-file log driver)

```bash
## Example

# Attaching to wordpress_mariadb_1, wordpress_wordpress_1
# wordpress_1  |
# mariadb_1    | mariadb 13:49:56.13
# wordpress_1  | Welcome to the Bitnami wordpress container
# mariadb_1    | mariadb 13:49:56.13 Welcome to the Bitnami mariadb container
# wordpress_1  | Subscribe to project updates by watching https://github.com/bitnami/bitnami-docker-wordpress
# ...
```

cf. [docker log doc](https://docs.docker.com/config/containers/logging/).

**Only if** logging driver is json-file, can use

```bash
docker logs CONTAINER_NAME
# Same for services, KO w. syslog
docker service logs CONTAINER_NAME
```

Note: Using syslog log driver, both mariadb & wp containers produces no output in syslog.

Solution: Force json-file log driver for both containers:

```yaml
services:
  BOTH_maria_and_wp:
    logging:
      driver: "json-file"
      options:
        max-size: "50m"
```

and use classic docker logs:

```bash
# Get service name
docker service ls

# Print logs
docker service logs WP_SERVICE_NAME
docker service logs MARIADB_SERVICE_NAME

# test-wordpress_wordpress.1.6g0kwqlwwaea@ns371715    | Welcome to the Bitnami wordpress container
# test-wordpress_wordpress.1.6g0kwqlwwaea@ns371715    | Subscribe to project updates by watching https://github.com/bitnami/bitnami-docker-wordpress
# test-wordpress_wordpress.1.6g0kwqlwwaea@ns371715    | Submit issues and feature requests at https://github.com/bitnami/bitnami-docker-wordpress/issues
# test-wordpress_wordpress.1.6g0kwqlwwaea@ns371715    |
# test-wordpress_wordpress.1.6g0kwqlwwaea@ns371715    | WARN  ==> You set the environment variable ALLOW_EMPTY_PASSWORD=yes. For safety reasons, do not use this flag in a production environment.
# test-wordpress_wordpress.1.6g0kwqlwwaea@ns371715    | nami    INFO  Initializing apache
# test-wordpress_wordpress.1.6g0kwqlwwaea@ns371715    | nami    INFO  apache successfully initialized
# test-wordpress_wordpress.1.6g0kwqlwwaea@ns371715    | nami    INFO  Initializing mysql-client
# ...
```

## Logs policy

In order to avoid duplicates, I won't redirect all logs into a dedicated volume.

All logs are already logged into required named volume, thanks to bitnami using some unix links to /bitnami/*
