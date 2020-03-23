# Monter un compose Nginx & PHP, avec sources bindées

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/01-docker/04-my-tests/02-compose-nginx-php
> docker-compose up
```

Résultat sur [localhost:8080](http://localhost:8080/)

Sources :

- *Fichier docker-compose.yml*
- *Dossier sources /src*
- *Dossier configs /config*
  - Contient également les configurations par défaut des deux images

## Notes problèmes rencontrés

Énormément de mal à mettre ça en place, du aux changements des images (configurations fournies dans les tutos ~obsolètes & exemples KO).

Le principal problème rencontré venait du fait que les fichiers .php n'étaient pas reconnus/interprétés/trouvés, et cela est du à la **configuration par défaut de (l'image) nginx**, celle qui permet d'afficher la page de garde dans `/usr/share/nginx/html`.

Cette configuration située dans `CONTAINER/etc/nginx/conf.d/default.conf` définit le comportement par défaut pour **localhost:80**, ce qui fait que nos configurations supplémentaires sur cette même adresse entraînent un conflit, et ne sont donc pas utilisées..

Solution : écraser ce fichier par un fichier vide, puis ajouter notre confiuration dans `CONTAINER/etc/nginx/conf.d/wtv.conf` (via volume bind).

## Principales commandes

Lancement en tant que service via [swarm](https://docs.docker.com/get-started/part4/)

```bash
> docker stack deploy -c docker-compose.yml swarm-php
```

Arrêt du service

```bash
> docker stack rm swarm-php
```

Notes

- Possibilité de lancer via `docker-compose up` pour avoir de meilleurs logs des différents containers.

---

Rentrer dans un container monté pour vérifier contenu ou conf :

```bash
// Récupérer le nom du container (Selec + clic droit (copier), puis clic droit (coller))
> docker container ls

// php-fpm
> docker exec -it NOM_CONTAINER bash

// **alpine ne possède pas bash (nginx)**
> docker exec -it NOM_CONTAINER /bin/ash
```

## Création des sources

- Ajout d'un fichier index.html sans PHP ni rieng, pour tester la bonne configuration de nginx
- Ajout d'un fichier index.php avec un poil de php, pour tester la bonne configuration de nginx & php
- Ajout d'un fichier test-php.php avec un poil de php, pour tester la bonne configuration des routes de nginx

## Ajout de phpay

*Rappel, php n'est pas un serveur web, juste ça transforme `<?php echo 'hey'; ?>` en `hey`.*

Notes

- Pour les serveurs web, PHP n'est plus recommandé, mais plutôt [PHP-FPM](https://hub.docker.com/r/bitnami/php-fpm/).
- Beaucoup de tests et d'erreurs, ci-dessous la version ultra condensée. Je reco de suivre [ce tuto](http://geekyplatypus.com/dockerise-your-php-application-with-nginx-and-php7-fpm/)

PHP-FPM est lancé avec Nginx dans la doc > Maj du docker-compose.

Le plan :

1. Monter Nginx, (analyser et) avoir accès à sa configuration (bind volume), modifier le dossier par défaut
2. Ajouter les sources (bind volume, pour dev)
3. Ajouter php-fpm
   - (analyser et avoir accès à sa conf., même si pas utile)
   - Ajouter un réseau et lier nginx & php-fpm
4. Modifier la configuration de Nginx pour interpréter le php sur les pages *.php

### Nginx & conf

Création du `docker-compose.yml` (Nginx + custom /src + conf)

```bash
version: '3.7'

# http://geekyplatypus.com/dockerise-your-php-application-with-nginx-and-php7-fpm/
services:
  web:
    # latest when writing
    image: nginx:1.17.6-alpine
    ports:
      - '8080:80'
    volumes:
      # Project files, bind mount for dev (allow files updates on refresh)
      - type: bind
        source: ./src
        # Custom src path in container
        # Nginx custom conf in HOST/config/nginx1.17.6-alpine/sites-enabled/site.conf
        target: /home/www
      # Replace nginx default conf with blank file to prevent localhost:80 conflict
      - type: bind
        source: ./config/nginx1.17.6-alpine/etc/nginx/conf.d/default.conf
        target: /etc/nginx/conf.d/default.conf
      # Config override
      # Autommatically loaded thanks to default nginx.conf > 'include /etc/nginx/conf.d/*.conf;'
      - type: bind
        source: ./config/nginx1.17.6-alpine/etc/nginx/conf.d/site.conf
        target: /etc/nginx/conf.d/site.conf
```

La configuration principale de nginx est située dans `CONTAINER/etc/nginx/nginx.conf`, et cette dernière inclus tous les `*.conf` situées dans `CONTAINER/etc/nginx/conf.d/`, cf. `HOST/config/`

On la stocke en local, afin que celle du container soit remplacée via volume bind.

**Note** : Malgré le bind, on ne peut pas modifier la configuration à la volée !

- Les fichiers sont bien modifiés en direct
- **Mais le service n'est pas relancé dans le conteneur !**

Création de `/etc/nginx/conf.d/site.conf` :

```conf
# http://geekyplatypus.com/dockerise-your-php-application-with-nginx-and-php7-fpm/
# Automatically loaded thanks to default nginx conf in /etc/nginx/nginx.conf > 'include /etc/nginx/conf.d/*.conf;'

server {
    index           index.html;

    # server_name   php-docker.local;
    server_name     localhost;

    error_log       /var/log/nginx/error.log;
    access_log      /var/log/nginx/access.log;

    # Répertoire par défaut des sources
    # root            /code;
    root            /home/www;
}

```

On **éclate la conf** par défaut `/etc/nginx/conf.d/default.conf`, cf. Problèmes :

```bash
# nope
```

### Php-fpm & conf

Maj du `docker-compose.yml`

```yaml
  phpfpm:
    # latest when writing
    image: 'php:7.4.1-fpm'
    volumes:
      # Project files, bind mount for dev
      # Php-fpm needs access to sources files to, else he can't access them due to containerisation
      - type: bind
        source: ./src
        target: /home/www
```

La configuration principale de php-fpm est située dans `CONTAINER/usr/local/etc/php-fpm.conf`, et cette dernière inclus tous les `*.conf` situées dans `CONTAINER/etc/php-fpm.d/`, cf. `HOST/config/` .

Maj du `docker-compose.yml`, ajout du réseau

```yaml
# tronqué af
services:
  web:
    image: nginx:1.17.6-alpine
    networks:
      - network-php
  
  phpfpm:
    # latest when writing
    image: 'php:7.4.1-fpm'
    networks:
      - network-php

networks:
  network-php:
```

### Nginx > conf php

Mise à jour de `/etc/nginx/conf.d/site.conf` :

```conf
# http://geekyplatypus.com/dockerise-your-php-application-with-nginx-and-php7-fpm/
# Automatically loaded thanks to default nginx conf in /etc/nginx/nginx.conf > 'include /etc/nginx/conf.d/*.conf;'

server {
    # Ajout de l'index.php
    index index.php index.html;

    server_name     localhost;

    error_log       /var/log/nginx/error.log;
    access_log      /var/log/nginx/access.log;

    # Répertoire par défaut des sources
    root            /home/www;

    # Gestion des autres pages != index.php
    location / {
        try_files $uri $uri/ /index.php?q=$uri&$args;
    }

    # Conf NGINX pour PHP
    location ~ \.php$ {
        try_files                   $uri =404;
        fastcgi_split_path_info     ^(.+\.php)(/.+)$;

        # 'phpfpm' doit correspondre au nom déclaré dans docker-compose.yml (IP en tant que variable d'env, grâce à docker)
        fastcgi_pass                phpfpm:9000;
        fastcgi_index               index.php;
        include                     fastcgi_params;
        fastcgi_param               SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param               PATH_INFO $fastcgi_path_info;
    }
}
```

Du coup, index.php est ok, mais lors du clic sur le lien test-php.php, ça télécharge plutôt que d'afficher la page..

A priori lié à la conf Nginx, particulièrement

```conf
    location / {
        try_files $uri $uri/ /index.php?q=$uri&$args;
    }
```

Mais également potentiellement à un problème de cache (wat -_- ..), cela ne le fait pas en [navigation privée](https://www.digitalocean.com/community/questions/php-files-are-downloading-instead-of-executing-on-nginx).

- Inspecteur > Network > [x] Disable cache
- Sinon gérer headers

Et normalement c'est tout good.

---

## Anciens tests

### Création de la configuration de Nginx afin d'interpréter le PHP

- *config/nginx/nginx.conf*

Attention, utilisation de l'image [Nginx alpine](https://hub.docker.com/_/nginx), et non bitnami nginx ; avec modification de la configuration en conséquence.

~Différents essais de configuration, mais KO. Lancement du container en interactif et récupation de la config de base pour modification (ne pas oublier le [shell alpine](https://stackoverflow.com/questions/35689628/starting-a-shell-in-the-docker-alpine-container)).

```bash
> docker run -it --rm \
  --name nginxalakon \
  -p 8080:80 \
  nginx:1.17.6-alpine \
  /bin/ash
```

et extraction de la config de base */etc/nginx/nginx.conf* dans */config/nginx/conf-from-nginx-alpine* // Ne sers à rien dans notre contexte

Il y a un include, même tarif pour */etc/nginx/conf.d/default.conf* dans */config/nginx/confd-default-from-nginx-alpine.conf* // Contient des exemples pour implémentation de php-fpm /o/.

Note : **Du coup, on surcharge uniquement default.conf**

Cette surcharge attend un serveur php/php-fpm sur un ip donnée, au port 9000 : `fastcgi_pass				127.0.0.1:9000;`

Du coup, via le compose on va monter un serveur php-fpm, dont l'ip sera remplacée par son nom 'phpfpm' (*docker-compose.yml > ligne ~25), que nous réutiliserons dans la conf nginx.

Note : **Pas mal de tests, tous KO > Recherche d'un example qui fonctionne**

- [Exemple mais KO/outdated](https://github.com/pisarevaa/docker-nginx-php-fpm) / Utilisation pour fouiller dans les fichiers de conf..
- [Mise en place d'un truc avec nginx et php](https://github.com/PrivateBin/docker-nginx-fpm-alpine) / idem

Il semble recommandé d'avoir des réplicas de dossiers contenus dans l'arbo (notamment pour surcharger les configs via bind) ex avec nginx >

```conf
\nginx\
  \conf.d\
    default.conf
  \sites-available\
    site.conf
  \sites-enabled\
  nginx.conf
```

- [Conf nginx reco linux ?](https://wiki.archlinux.org/index.php/Nginx)
- [+1 php-fpm](https://wiki.archlinux.org/index.php/Nginx#FastCGI)

** Avec possibilité de C/C le nom du container depuis la console ( `> docker container ls` > Sélectionner > clic droit (copier) > clic droit (coller))

Note : la conf de php-fpm dans l'image docker est dans `/usr/local/etc/`

Clean : Config > defaults + surcharges

// Essayer sans sources bindées > nouveau fichier index.php (> <?= phpinfo(); ?> ) depuis intérieur container

// KO

---

Essai avec un [autre tuto](http://geekyplatypus.com/dockerise-your-php-application-with-nginx-and-php7-fpm/) // ♥ ♥ ♥

1. Mise en place de nginx basique // ok
2. Déplacement du dossier par défaut de nginx // ok
   - Vérification dans le container `docker exec -it NOM_CONTAINER /bin/ash`
   - `cd /home/www`
   - `ls // Les fichiers sont bien la`
   - index.html ok sur [http://localhost:8080/](http://localhost:8080/)
3. Ajout de php-fpm
   - Ne pas oublier le réseau
   - Ajout de la conf recommandée et test // index.html OK, index.php 404 // Note : c'est normal
   - Ajout du volume sur php-fpm
   - Tjr KO

Note : utiliser `docker compose up` plutôt que `docker stack deploy` afin d'avoir de meilleurs logs.

- index.php non trouvé car il ne tape pas au bon endroit : il tape sur le defaut nginx `/usr/share/nginx/html/`
  - Retrait de `config/nginx/conf.d/default.conf` .. qui fout peut être la marde // non // EDIT : OUI
    - Par défaut ce fichier est inclus dans l'image de nginx, et il définit localhost:80 du container. Du coup si on le redéfinit via un autre fichier dans conf.d, il y a conflit, et c'est le défaut (ou le premier par ordre alpha ?) qui prend le pas
    - **Solution : Ecraser le fichier par le même fichier vide.**

Du coup, index.php est ok, mais lors du clic sur le lien test-php.php, ça télécharge plutôt que d'afficher la page..

- Problème de cache (wat -_- ..), cela ne le fait pas en [navigation privée](https://www.digitalocean.com/community/questions/php-files-are-downloading-instead-of-executing-on-nginx).
  - Inspecteur > Network > [x] Disable cache
  - Sinon gérer headers

// Need clean tout le bordel mais instructif
