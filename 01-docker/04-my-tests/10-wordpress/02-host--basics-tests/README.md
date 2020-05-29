# Faire tourner WP avec les bases fournies par la doc

Sur le serveur, seulement bitnami.

A voir si ca roule correctement DE BASE sur l'architecture, avant de goyer.

- ✅ Renommer ~propre (test-XXX) avant de lancer
- ✅ Tester avec docker run
- En faire un docker-compose

## Bitnami WP

```bash
# Créer le réseau wp/db
docker network create test-wordpress

# Volume pour les données bdd
docker volume create --name test-wordpress-db

# Lancement du conteneur bdd
docker run -d --name test-wordpress-mariadb \
  --env ALLOW_EMPTY_PASSWORD=yes \
  --env MARIADB_USER=bn_wordpress \
  --env MARIADB_DATABASE=bitnami_wordpress \
  --network test-wordpress \
  --volume test-wordpress-db:/bitnami/mariadb \
  bitnami/mariadb:latest

# Volume pour les données wp
docker volume create --name test-wordpress-datas

# Lancement du conteneur wp
docker run -d --name test-wordpress \
  -p 8080:8080 -p 8443:8443 \
  --env ALLOW_EMPTY_PASSWORD=yes \
  --env MARIADB_HOST=test-wordpress-mariadb \
  --env WORDPRESS_DATABASE_USER=bn_wordpress \
  --env WORDPRESS_DATABASE_NAME=bitnami_wordpress \
  --network test-wordpress \
  --volume test-wordpress-datas:/bitnami/wordpress \
  bitnami/wordpress:latest

# Test wo internet publication
curl http://localhost:8080/ || exit 1

## OK !
# <!DOCTYPE html>
# <html class="no-js" lang="en-US">
#   <head>
#     <meta charset="UTF-8">
#     <meta name="viewport" content="width=device-width, initial-scale=1.0" >
#     <link rel="profile" href="https://gmpg.org/xfn/11">
#     <title>User&#039;s Blog! &#8211; Just another WordPress site</title>
# ...
```

## Coupay

```bash
docker container stop test-wordpress-mariadb && \
docker container stop test-wordpress && \
docker container rm test-wordpress-mariadb && \
docker container rm test-wordpress

docker system prune

docker volume rm test-wordpress-db && \
docker volume rm test-wordpress-datas
```
