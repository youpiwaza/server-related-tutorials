# Monter un compose Nginx, PHP, SQL & Admin

Pour tester : `docker-compose up`

Sources :

- *Fichier docker-compose.yml*
- *Dossier sources /src*
- *Dossier configs /config*
  - Contient également les configurations par défaut des deux images

On repart du compose nginx + php :)



## Principales commandes

Lancement en tant que service via [swarm](https://docs.docker.com/get-started/part4/)

```
> docker stack deploy -c docker-compose.yml swarm-php-sql
```

Arrêt du service

```
> docker stack rm swarm-php-sql
```

**Notes**

- Possibilité de lancer via `docker-compose up` pour avoir de meilleurs logs des différents containers.

---

Rentrer dans un container monté pour vérifier contenu ou conf :

```
// Récupérer le nom du container (Selec + clic droit (copier), puis clic droit (coller)) 
> docker container ls 

// php-fpm 
> docker exec -it NOM_CONTAINER bash 

// **alpine ne possède pas bash (nginx)**
> docker exec -it NOM_CONTAINER /bin/ash 
```



## Choix de la DB et de l'admin

Basiquement le choix se fait entre [MySQL](https://hub.docker.com/_/mysql) et [MariaDB](https://hub.docker.com/_/mariadb).

Après avoir lu [quelques comparatifs](https://www.eversql.com/mariadb-vs-mysql/) et [benchmarks](http://dimitrik.free.fr/blog/archives/2018/04/mysql-performance-80-and-utf8-impact.html), les différences restent mineures.

Je vais partir sur MySQL parce que cela semble légèrement plus performant, que j'en ai plus l'habitude & qu'il peut y avoir de légères variations de syntaxe via mariaDB ?

Concernant le choix de l'admin, les deux recommandent [adminer](https://hub.docker.com/_/adminer) (ex: phpmyadmin), je vais partir la dessus pour mes tests.



## Mise en place de MySQL

Lecture de la doc et récupération des bonnes pratiques :

1. Analyse de la configuration de l'image
2. Initialisation / Récupération de la base de données
  - Variables d'environnements + volumes
3. Sécurité : Utilisation de [docker secrets](https://docs.docker.com/compose/compose-file/#secrets)


### Ajout de MySQL au docker-compose

```
db:
    command: --default-authentication-plugin=mysql_native_password
    environment:
      MYSQL_ROOT_PASSWORD: pizza
    # latest when writing
    image: 'mysql:8.0.18'
    restart: always
```

Ok, mais quelques warnings de configuration à régler



## Ajout de adminer

... pour pouvoir mettre les mains dedans.

```
adminer:
    # latest when writing
    image: 'adminer:4.7.5'
    ports:
      # Running on port 8081 as 8080 is already used by web
      - 8081:8080
    restart: always
```

Ok, test sur [http://localhost:8081/](http://localhost:8081/), on peut se connecter via root/pizza.

---

*Essai adminer via fast-cgi > echec*

Mais tourne sur son propre serveur. La doc recommande de le passer sur le php fast-cgi.

// Multiples essais [1](https://github.com/TimWolla/docker-adminer/issues/35), [2](https://forum.vestacp.com/viewtopic.php?t=14386&start=10) > KO, doc incomplète

Retour sur l'image adminer de base, je pense que adminer fast-cgi dispose de son propre serveur, je n'arrive pas a lui dire d'utiliser l'autre ou de le faire tourner sans conflit...

Essai de l'ouvrir sur `/adminer` plutôt que sur `localhost:8081` via un [proxy](https://gist.github.com/soheilhy/8b94347ff8336d971ad0) et son propre fichier de conf > KO

Bref, on avance..

---



## Configuration MySQL

On rentre dans le conteneur mysql et on suit la doc

```
> docker exec -it NOM_CONTAINER_MYSQL bash
```

Copie des 3 fichiers présents dans `HOST/config/mysql8.0.18/_defaults/etc/mysql/` mais rien d'extravagant.

Possibilité de rajouter/surcharger dans `/etc/mysql/conf.d/wtv.cnf`.


### Ajout de l'encodage par défaut :

Utilisation de [l'encodage recommandé](https://dev.mysql.com/doc/refman/5.5/en/charset-unicode-utf8mb4.html) en suivant les recos [SO](https://stackoverflow.com/a/3513812/12026487).

Création d'un fichier de conf personnalisé `my-conf.cnf` et surcharge de la configuration dans le docker-compose.

Test > Ok :)



## Base de données

### Initialisation









































//

