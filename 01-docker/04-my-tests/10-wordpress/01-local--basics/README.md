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
```
