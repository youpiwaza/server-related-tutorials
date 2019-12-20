# Volumes

[Tutoriaux officiels](https://docs.docker.com/storage/volumes/)



## Création d'une image pour les tests

Utilisation d'une image nginx sur alpine, avec un fichier index.html alakon

[Doc nginx sur dockerhub](https://hub.docker.com/_/nginx)

Création du Dockerfile

- Attention, contrairement à ce qui est indiqué dans la doc, le fichier Dockerfile ne doit pas être dans le dossier, mais à l'extérieur

Création de l'image

`docker build -t some-content-nginx .`

Lancement du conteneur sur le port 8080

`docker run --name test-volumes-nginx -d -p 8080:80 some-content-nginx`

Image en place avec contenu statique sans contenu :) [hey](http://localhost:8080/)



## Volume

Création d'un volume dédié

`docker volume create test-my-vol`

Vérifications

```
docker volume ls
docker volume inspect test-my-vol
```

--- 

Monter le volume sur une image nginx vierge

```
docker run -d \
  --name test-volumes-nginx2 \
  --mount source=test-my-vol,target=/app \
  nginx:1.17.6-alpine
```

Inspect ok, arret & destruction container & volume

--- 

Monter le volume sur un service

```
docker service create -d \
  --replicas=4 \
  --name devtest-service \
  --mount source=myvol2,target=/app \
  nginx:1.17.6-alpine
```

--- 

Populer un volume vierge avec le contenu d'un conteneur (ex: la page par défaut de nginx)

```
docker run -d \
  --name=nginxtest \
  --mount source=nginx-vol,destination=/usr/share/nginx/html \
  nginx:1.17.6-alpine
```

Et doc > destruction directe O_o

Je veux regarder a l'intérieur, après un peu de recherche, utilisation de [docker cp](https://docs.docker.com/engine/reference/commandline/cp/)

```
# docker cp NomContainer:CheminContainer CheminHôte
docker cp nginxtest:/usr/share/nginx/html .
```

Récupération du dossier /html depuis le conteneur vers l'hôte, avec la page par défaut de nginx, mais rien à voir avec les volumes...

Après un [peu de recherche](https://github.com/moby/moby/issues/25245), ptet monter un container en lui attachant le volume et check depuis le conteneur.. (ou avec plugins docker)

[Doc alpine](https://hub.docker.com/_/alpine/)

Utilisation d'un conteneur alpine anonyme, qui se détruira après utilisation, en mode intéractif, avec lancement de son propre shell à la montée du conteneur.

Monté avec le volume précédemment associé à nginx

```
> docker run --rm -i -t \
  --mount source=nginx-vol,destination=/home/mahVolume \
  alpine:latest \
  /bin/sh

// Dans le conteneur
># cd /home/mahVolume
># ls

// Affiche
50x.html    index.html
```

Impec' georgette ! Le conteneur Nginx à bien populé le volume vide.

---

Possibilité de connecter un conteneur à un volume en lecture seule (peut être pas mal pour faire des stats..)

*Non testé*

---

Possibilité de créer des volumes partagés pour swarm.

*Non testé*

---

Avant de passer à la suite (backups), essai de population de volume depuis l'hôte

~~Duplication du Dockerfile nginx avec site de base, pour y associer un volume (et les ports de base..)~~

// Dockerfile VOLUME ne correspond plus du tout à la doc --mount


```
// Suppression du conteneur en cours, suppression du volume 
// Création du nouveau volume (vide) 
>  docker volume create nginx-vol 

// Création du même conteneur mais lien du volume vide vers le dossier /html statique
> docker run -d \
  --name test-volumes-nginx  \
  --mount source=nginx-vol,destination=/usr/share/nginx/html \
  -p 8080:80  \
  some-content-nginx
```

*Résultat*

- localhost:8080 > le contenu du site injecté lors de la création de l'image ("Heya..")
- Utilisation de alpine bash > cd /home/mahVolume/
- On récupère bien le nouveau site ("Heya..") : le conteneur à bien populé le volume
  - En l'occurence dans --mount, la destination (dans le dossier du nouveau conteneur..) devient la source

**Test de l'obfuscation**

Nouvelle image Dockerfile à partir d'un autre dossier. Le contenu html est différent ("Un autre site")

```
// Arrêt et suppression du conteneur en cours, conservation du volume 
// Création du nouveau build
>  docker build \
  -f Dockerfile3 \
  -t some-other-content-nginx .
  

// Création du même conteneur (build avec nouveau contenu) mais lien du volume rempli  vers le dossier /html statique
> docker run -d \
  --name test-volumes-other-nginx  \
  --mount source=nginx-vol,destination=/usr/share/nginx/html \
  -p 8080:80  \
  some-other-content-nginx
```

*Résultat*

- L'ancien contenu est affiché ("Heya..") : le volume a obfusqué le nouveau contenu 

```
// Arrêt et suppression du conteneur en cours
// Création du même conteneur (même build avec nouveau contenu) mais sans lien du volume
> docker run -d \
  --name test-volumes-other-nginx  \
  -p 8080:80  \
  some-other-content-nginx
```

*Résultat*

- Le nouveau contenu est bien affiché ("Un autre site..") : le contenu n'a pas été détruit dans le build (ce qui parait logique en fait..)















//

