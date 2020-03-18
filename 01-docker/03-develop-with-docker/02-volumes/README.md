# Volumes

[Tutoriaux officiels](https://docs.docker.com/storage/volumes/)

## Création d'une image pour les tests

Utilisation d'une image nginx sur alpine, avec un fichier index.html alakon

[Doc nginx sur dockerhub](https://hub.docker.com/_/nginx)

Création du Dockerfile

- Attention, contrairement à ce qui est indiqué dans la doc, le fichier Dockerfile ne doit pas être dans le dossier, mais à l'extérieur

Création de l'image

`> docker build -t some-content-nginx .`

Lancement du conteneur sur le port 8080

`> docker run --name test-volumes-nginx -d -p 8080:80 some-content-nginx`

Image en place avec contenu statique sans contenu :) [hey](http://localhost:8080/)

## Volume

Création d'un volume dédié

`> docker volume create test-my-vol`

Vérifications

```bash
> docker volume ls
> docker volume inspect test-my-vol
```

---

Monter le volume sur une image nginx vierge

```bash
> docker run -d \
  --name test-volumes-nginx2 \
  --mount source=test-my-vol,target=/app \
  nginx:1.17.6-alpine
```

Inspect ok, arret & destruction container & volume

---

Monter le volume sur un service

```bash
> docker service create -d \
  --replicas=4 \
  --name devtest-service \
  --mount source=myvol2,target=/app \
  nginx:1.17.6-alpine
```

---

Populer un volume vierge avec le contenu d'un conteneur (ex: la page par défaut de nginx)

```bash
> docker run -d \
  --name=nginxtest \
  --mount source=nginx-vol,destination=/usr/share/nginx/html \
  nginx:1.17.6-alpine
```

Et doc > destruction directe O_o

Je veux regarder a l'intérieur, après un peu de recherche, utilisation de [docker cp](https://docs.docker.com/engine/reference/commandline/cp/)

```bash
# docker cp NomContainer:CheminContainer CheminHôte
> docker cp nginxtest:/usr/share/nginx/html .
```

Récupération du dossier /html depuis le conteneur vers l'hôte, avec la page par défaut de nginx, mais rien à voir avec les volumes...

Après un [peu de recherche](https://github.com/moby/moby/issues/25245), ptet monter un container en lui attachant le volume et check depuis le conteneur.. (ou avec plugins docker)

[Doc alpine](https://hub.docker.com/_/alpine/)

Utilisation d'un conteneur alpine anonyme, qui se détruira après utilisation, en mode intéractif, avec lancement de son propre shell à la montée du conteneur.

Monté avec le volume précédemment associé à nginx

```bash
> docker run --rm -i -t \
  --mount source=nginx-vol,destination=/home/mahVolume \
  alpine:latest \
  /bin/sh

#  Dans le conteneur
# > cd /home/mahVolume
# > ls

# Affiche
# 50x.html    index.html
```

Impec' georgette ! Le conteneur Nginx à bien populé le volume vide.

---

Possibilité de connecter un conteneur à un volume en lecture seule (peut être pas mal pour faire des stats..)

Non testé

---

Possibilité de créer des volumes partagés pour swarm.

Non testé

---

### Tests population & comportement obfuscation

obfuscation = télatépula = caché mais non détruit

Avant de passer à la suite (backups), essai de population de volume depuis l'hôte

~~Duplication du Dockerfile nginx avec site de base, pour y associer un volume (et les ports de base..)~~

// Dockerfile VOLUME ne correspond plus du tout à la doc, utilisation de --mount

- Docker compose semble supporter la nouvelle syntaxe..
- Compliqué de changer les ports de base de Nginx via DF

```bash
# Suppression du conteneur en cours, suppression du volume
# Création du nouveau volume (vide)
>  docker volume create nginx-vol

# Création du même conteneur mais lien du volume vide vers le dossier /html statique
> docker run -d \
  --name test-volumes-nginx  \
  --mount source=nginx-vol,destination=/usr/share/nginx/html \
  -p 8080:80  \
  some-content-nginx
```

Résultat

- localhost:8080 > le contenu du site injecté lors de la création de l'image ("Heya..")
- Utilisation de alpine bash > cd /home/mahVolume/
- On récupère bien le nouveau site ("Heya..") : le conteneur à bien populé le volume
  - En l'occurence dans --mount, la destination (dans le dossier du nouveau conteneur..) devient la source

#### Test de l'obfuscation

Nouvelle image Dockerfile à partir d'un autre dossier. Le contenu html est différent ("Un autre site")

```bash
# Arrêt et suppression du conteneur en cours, conservation du volume
# Création du nouveau build
>  docker build \
  -f Dockerfile3 \
  -t some-other-content-nginx .
  

# Création du même conteneur (build avec nouveau contenu) mais lien du volume rempli  vers le dossier /html statique
> docker run -d \
  --name test-volumes-other-nginx  \
  --mount source=nginx-vol,destination=/usr/share/nginx/html \
  -p 8080:80  \
  some-other-content-nginx
```

Résultat

- L'ancien contenu est affiché ("Heya..") : le volume a obfusqué le nouveau contenu

```bash
# Arrêt et suppression du conteneur en cours
# Création du même conteneur (même build avec nouveau contenu) mais sans lien du volume
> docker run -d \
  --name test-volumes-other-nginx  \
  -p 8080:80  \
  some-other-content-nginx
```

Résultat

- Le nouveau contenu est bien affiché ("Un autre site..") : le contenu n'a pas été détruit dans le build (ce qui parait logique en fait..)

#### Build depuis un repo

[Doc docker build](https://docs.docker.com/engine/reference/commandline/build/#extended-description)

Création d'un [repo avec un site minimaliste](https://github.com/youpiwaza/test-min-static-site)

Création d'un nouveau build qui va copier depuis le dossier courant .

```bash
# Le contexte est définit sur le repo, branche master, dossier "site" (d'où la copie du dossier courant)
# KO, plus d'accès au contexte local, donc plus accès au Dockerfile4
>  docker build \
  -f Dockerfile4 \
  -t some-repo-content-nginx \
  https://github.com/youpiwaza/test-min-static-site#master:site

# NE PAS OUBLIER LE .git, on ne passe pas l'url du repo, mais l'url à cloner
>  docker build \
  -t some-repo-content-nginx \
  https://github.com/youpiwaza/test-min-static-site.git#master:site
  
> docker run -d \
  --name test-repo-nginx  \
  -p 8081:80  \
  some-repo-content-nginx
```

[localhost:8081 > Profit](http://localhost:8081/)

Cay vraiment kewl

### Backups & restauration

Création de conteneur et de volumes anonymes à la volée, pour effectuer des actions sur des volumes (pas d'accès direct au volumes sans plugins)

Note : l'argument `--rm` permet de supprimer automatiquement les volumes anonymes à la destruction du conteneur auquel il est attaché.

#### Sauvegarde de volumes

Possibilité d'utiliser `--volumes-from` en argument de `docker run` afin de monter un conteneur avec un volume pré-éxistant.

- Possibilité de monter directement un volume (pas besoin qu'il soit rattaché pour sauvegarder)

One liner backup

1. On crée un conteneur qui va
2. *bind (volume)* > se connecter à un répertoire de l'hôte (via `-v`, dans le répertoire `/backup`)
3. monter le volume à sauvegarder (à partir du conteneur auquel il est attaché),
   - Je pense qu'il reste possible de passer par mount..
4. faire une copie de son contenu,
5. la zipper,
6. et la copier à la fois dans le répertoire `/backup`, ainsi que dans le répertoire courant (`$(pwd)`) de l'hôte (grâce au bind)
7. Puis le conteneur est détruit, vu qu'il ne fait plus rien

```bash
# docker run --rm --volumes-from dbstore -v $(pwd):/backup ubuntu tar cvf /backup/backup.tar /dbdata

# Attention, on passe le conteneur concerné avec --volume-from, et NON le volume, NI le nom du build !
# En gros le tout dernier paramètre c'est le répertoire (contenu dans le volume) à sauvegarder
# Le reste ne bouge pas
> docker run --rm \
  --volumes-from test-volumes-nginx \
  -v $(pwd):/backup \
  alpine:latest \
  tar cvf /backup/backup.tar /usr/share/nginx/html
```

---

Essai en montant correctement les volumes (pour *bind* on rajoute `type=bind,`)

```bash
> docker run --rm \
  --mount source=nginx-vol,destination=/home/volumeContent \
  --mount type=bind,source=$(pwd),target=/backup \
  alpine:latest \
  tar cvf /backup/backup2.tar "/home/volumeContent"
```

OK :D

En gros

1. On monte le *volume* (directement, sans passer par le conteneur) "nginx-vol", dans le dossier "/home/volumeContent" de notre nouveau conteneur
2. On *bind* le répertoire courant de l'hôte (~dossier courant du terminal, en passant par le cli) au répertoire "/backup" du conteneur
3. On zip  "/home/volumeContent" dans le fichier "backup2.tar", qui se trouvera dans "/backup"
4. Cela copie dans le répertoire courant grâce au *bind*.

#### Restaurer

One liner

```bash
> docker run --rm --volumes-from dbstore2 -v $(pwd):/backup ubuntu bash -c "cd /dbdata && tar xvf /backup/backup.tar --strip 1"
```

1. Nouveau conteneur, monté avec le même volume que le conteneur  dbstore2
2. *bind* le repertoire courant de l'hôte avec le répertoire "/backup" du nouveau conteneur
3. On demande au shell d'exécuter
   1. Go dans "/dbdata" // Le répertoire arbitraire ou sont stockées les données du build, correspond à "/usr/share/nginx/html" dans les exemples précédents
   2. Y extraire "/backup/backup.tar" (Le fichier est présent dans le novueau conteneur car il est également présent dans le répertoire courant de l'hôte (*bind*))

non testé, + meilleure écriture (cf. backup)

## Bind

[bind mounts](https://docs.docker.com/storage/bind-mounts/)

Grosso merdo même comportement que volume, mais le dossier *source* provient du conteneur source.

```bash
# Création d'un conteneur nginx alakon, avec le fichier html de base bindé à un fichier local
> docker run -d \
  --name=nginxtest \
  -p 8080:80 \
  --mount type=bind,source="$(pwd)"/to-bind-html-directory,target=/usr/share/nginx/html \
  nginx:1.17.6-alpine
```

1. Go sur le [localhost](http://localhost:8080/) // ok
2. Modifier le fichier local (hôte) ./to-bind-html-directory/index.html
3. Besoin de réactualiser afin que le navigateur répercute les changements :)

Note : Possibilité de *bind* un dossier différent des sources, typiquement un dossier /prod

---

Prochaine étape, faire tourner le HMR :

- A priori on peut passer par webpack ou par gulp
- Le but serait de bind le dossier hôte, et de faire tourner gulp-watch sur le container, sur le dossier bindé..
- [Vidéo grafikart environnement de dev](https://www.youtube.com/watch?v=F9R1EOaA7EA)

**CONFIGURATION VIA BIND !**

[Grafikart env de dev](https://youtu.be/F9R1EOaA7EA?t=1000)

On rajoute des fichiers dans le conteneur (ou on écrase des fichiers) ! On remplace la conf à la volée.

### Configuration du bind dans le fichier (docker compose)

- *Fichier docker-compose4.yml*
- *Dossier /to-bind-html-directory*

Création de l'image nginx de base, avec définition des ports

```yaml
version: '3.7'

services:
  test-compose-bind:
    image: nginx:1.17.6-alpine
    ports:
      - '8080:80'
```

Lancement en tant que service via [swarm](https://docs.docker.com/get-started/part4/)

```bash
> docker stack deploy -c docker-compose4.yml swarm-compose-bind
```

Test sur le [localhost](http://localhost:8080/) // ok */!\ Toujours une petite latence au lancement du service !*

Arrêt du service

```bash
> docker stack rm swarm-compose-bind
```

Ajout du bind

```yaml
  test-compose-bind:
    # [...]
    volumes:
      - type: bind
        source: ./to-bind-html-directory
        target: /usr/share/nginx/html
```

(Relancer service & tester (localhost > changer fichier > actualiser > okay))
