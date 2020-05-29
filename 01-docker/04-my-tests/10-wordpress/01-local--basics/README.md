# Faire tourner WP avec les bases fournies par la doc

En local. WP & bitnami OK.

## WordPress

```bash
# Créer le réseau wp/db
docker network create wp-network-offi

# Volume pour les données bdd
docker volume create --name bdd-wp-offi

# Lancement du conteneur bdd
docker run -d --name db-wp-offi \
  --env MYSQL_DATABASE=exampledb \
  --env MYSQL_USER=exampleuser \
  --env MYSQL_PASSWORD=examplepass \
  --env MYSQL_RANDOM_ROOT_PASSWORD='1' \
  --network wp-network-offi \
  --volume bdd-wp-offi:/var/lib/mysql \
  mysql:5.7

# Volume pour les données wp
docker volume create --name data-wp-offi

# Lancement du conteneur wp
docker run -d --name wordpress-offi \
  -p 8085:80 \
  --env WORDPRESS_DB_HOST=db-wp-offi \
  --env WORDPRESS_DB_USER=exampleuser \
  --env WORDPRESS_DB_PASSWORD=examplepass \
  --env WORDPRESS_DB_NAME=exampledb \
  --network wp-network-offi \
  --volume data-wp-offi:/var/www/html \
  wordpress:latest
```

[Local uri](http://localhost:8085/) // port choisi au pif, bitnami était déjà sur 8080

## Bitnami WP

```bash
# Créer le réseau wp/db
docker network create wordpress-network

# Volume pour les données bdd
docker volume create --name mariadb_data

# Lancement du conteneur bdd
docker run -d --name mariadb \
  --env ALLOW_EMPTY_PASSWORD=yes \
  --env MARIADB_USER=bn_wordpress \
  --env MARIADB_DATABASE=bitnami_wordpress \
  --network wordpress-network \
  --volume mariadb_data:/bitnami/mariadb \
  bitnami/mariadb:latest

# Volume pour les données wp
docker volume create --name wordpress_data

# Lancement du conteneur wp
docker run -d --name wordpress \
  -p 8080:8080 -p 8443:8443 \
  --env ALLOW_EMPTY_PASSWORD=yes \
  --env WORDPRESS_DATABASE_USER=bn_wordpress \
  --env WORDPRESS_DATABASE_NAME=bitnami_wordpress \
  --network wordpress-network \
  --volume wordpress_data:/bitnami/wordpress \
  bitnami/wordpress:latest
```

[Local uri](http://localhost:8080/)

## Rights verification

### WP Offi

```bash
# Go in da container
docker exec -it wordpress-offi bash

>> whoami
# root

>> ls -la
# all  www-data: www-data
```

### Bitnami

```bash
# Go in da container
docker exec -it wordpress bash

>> whoami
# whoami: cannot find name for user ID 1001

>> ls -la
# Mostly root

>> cd bitnami/wordpress
>> ls -la
## Mostly 1001:root
# -rw-r--r-- 1 1001 root    0 May 27 14:39 .initialized
# -rw-r--r-- 1 1001 root    0 May 29 11:27 .restored
# -r--r----- 1 1001 root 4033 May 27 14:39 wp-config.php
# drwxr-xr-x 6 1001 root 4096 May 27 14:48 wp-content
```

## Volumes content verification

```bash
# IDK much about mysql & maria file system so.. wtv
# WP db
docker run \
    -it \
    --mount \
        source=bdd-wp-offi,target=/home \
    --rm \
    --workdir /home \
    alpine \
    /bin/ash

# WP datas
docker run \
    -it \
    --mount \
        source=data-wp-offi,target=/home \
    --rm \
    --workdir /home \
    alpine \
    /bin/ash

>> ls -la
# -rw-r--r--    1 xfs      xfs            280 May 29 09:59 .htaccess
# -rw-r--r--    1 xfs      xfs            405 Feb  6 06:33 index.php
# -rw-r--r--    1 xfs      xfs          19915 Feb 12 11:54 license.txt
# -rw-r--r--    1 xfs      xfs           7278 Jan 10 14:05 readme.html
# -rw-r--r--    1 xfs      xfs           6912 Feb  6 06:33 wp-activate.php
# drwxr-xr-x    9 xfs      xfs           4096 Apr 29 18:58 wp-admin
## ... xfs > specific file system w. journald ? https://wiki.ubuntu.com/XFS
## Else this seems rather normal AF

# ---

# Bitnami db
docker run \
    -it \
    --mount \
        source=mariadb_data,target=/home \
    --rm \
    --workdir /home \
    alpine \
    /bin/ash
# ...

# Bitnami WP datas
docker run \
    -it \
    --mount \
        source=wordpress_data,target=/home \
    --rm \
    --workdir /home \
    alpine \
    /bin/ash

>> ls -la
# -r--r-----    1 1001     root          4033 May 27 14:39 wp-config.php
# drwxr-xr-x    6 1001     root          4096 May 27 14:48 wp-content
## Better rights management & reduced folders, only user related stuff
```

## Coupay

```bash
docker container stop db-wp-offi && \
docker container stop wordpress-offi && \
docker container stop mariadb && \
docker container stop wordpress && \
docker container rm db-wp-offi && \
docker container rm wordpress-offi && \
docker container rm mariadb && \
docker container rm wordpress

docker system prune

docker volume rm bdd-wp-offi && \
docker volume rm data-wp-offi && \
docker volume rm mariadb_data && \
docker volume rm wordpress_data
```
