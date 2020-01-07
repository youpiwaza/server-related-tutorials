# Traefik / Reverse proxy & UI

Pour tester : `docker-compose up`

Sources :

- *Fichier docker-compose.yml*
- *Fichier docker-compose-whoami.yml*



## Principales commandes

Lancement en tant que service via [swarm](https://docs.docker.com/get-started/part4/)

```bash
> docker stack deploy -c docker-compose.yml swarm-traefik
```

Arrêt du service

```bash
> docker stack rm swarm-traefik
```

```bash
# Attention, -f est avant up, le reste après --"
> docker-compose \
  -f docker-compose-whoami.yml \
  up \
  -d \
  --scale whoami=3
```

Puis test sur le [localhost r proxy](http://whoami.docker.localhost/)

**Notes**

- Possibilité de lancer via `docker-compose up` pour avoir de meilleurs logs des différents containers.

---

Rentrer dans un container monté pour vérifier contenu ou conf :

```bash
# Récupérer le nom du container (Selec + clic droit (copier), puis clic droit (coller)) 
> docker container ls 

# bash (bases ubuntu/classiques)
> docker exec -it NOM_CONTAINER bash 

# **alpine ne possède pas bash**
> docker exec -it NOM_CONTAINER /bin/ash 
```



## Ressources & tests

- [Site officiel](https://containo.us/traefik/)
- [Doc officielle](https://docs.traefik.io/)
- [Dockerhub](https://hub.docker.com/_/traefik)


Exemple dockerhub KO ?

Essai avec le [docker-compose de la doc](https://docs.traefik.io/getting-started/quick-start/) // OK :)


### Test via docker-compose

> Essai avec le swarm de nginx/php/sql en place sur 8080
> Changement de proxy pour l'interface pour [:8069](http://localhost:8069/) en attendant de faire une version définitive
> Les [routes générées](http://test-whoami.docker.localhost/) ne sont pas encore en place

Lancement via swarm `docker stack deploy -c docker-compose.yml swarm-traefik` et poursuite du tutoriel [RTFM](https://docs.traefik.io/getting-started/quick-start/).

Séparation du docker-compose whoami pour des raisons de convénience

```yaml
  whoami:
    # A container that exposes an API to show its IP address
    image: containous/whoami
    # Traefik default conf w. labels
    labels:
      - "traefik.http.routers.whoami.rule=Host(`whoami.docker.localhost`)"
```

```bash
# Attention, -f est avant up, le reste après --"
> docker-compose \
  -f docker-compose-whoami.yml \
  up \
  -d \
```

[Test reverse proxy](http://whoami.docker.localhost/) // yay

Test whoami avec des répliques

**Besoin d'une configuration spéciale pour swarm, pour le moment utiliser docker-compose CLI**

```bash
# Attention, -f est avant up, le reste après --"
> docker-compose \
  -f docker-compose-whoami.yml \
  up \
  -d \
  --scale whoami=3
```

Test sur le [localhost r proxy](http://whoami.docker.localhost/) // OK, + hostname & IP change


#### ~~Tests KO via swarm (labels KO)~~

Scale whoami, via docker stack, cf. [doc](https://docs.docker.com/engine/reference/commandline/service_scale/)

```bash
> docker service scale swarm-traefik_whoami=2
```

Labels KO > Traefik KO

Essai en fixant le nombre de répliues via docker-compose.yml

```yaml
whoami:
    deploy:
        replicas: 3
```

Mais les labels ne sont toujours pas en place > Traefik KO

```bash
> docker container ls
CONTAINER ID        IMAGE                          COMMAND                  CREATED             STATUS                  PORTS                 NAMES
126c4427cd90        containous/whoami:latest       "/whoami"                5 seconds ago       Up 1 second             80/tcp                swarm-traefik_whoami.2.pk8ql5jbf13d76c479onmluxb
5bd203d5d72b        containous/whoami:latest       "/whoami"                5 seconds ago       Up Less than a second   80/tcp                swarm-traefik_whoami.3.6flip3rs6j1ooywvo8g51l8j8
42746500cf26        containous/whoami:latest       "/whoami"                5 seconds ago       Up Less than a second   80/tcp                swarm-traefik_whoami.1.w6fanvsnrwkg7z9hklta5vcrz
```

Les noms sont ok en passant par docker-compose cli (reco docs) > Traefik OK

```bash
> docker-compose -f docker-compose-whoami.yml up -d whoami

> docker container ls
CONTAINER ID        IMAGE                          COMMAND                  CREATED             STATUS              PORTS                 NAMES
1702b37b7c98        containous/whoami              "/whoami"                5 seconds ago       Up 3 seconds        80/tcp                04-traefik_whoami_1

> docker-compose -f docker-compose-whoami.yml up -d --scale whoami=2

> docker container ls
CONTAINER ID        IMAGE                          COMMAND                  CREATED             STATUS              PORTS                 NAMES
2ec938934dde        containous/whoami              "/whoami"                5 seconds ago       Up 4 seconds        80/tcp                04-traefik_whoami_2
1702b37b7c98        containous/whoami              "/whoami"                25 seconds ago      Up 24 seconds       80/tcp                04-traefik_whoami_1
```

Mise en place de traefik + swarm dans le test suivant

Note : Je ne suis pas rentré dans la config (statique) de traefik ; je vais voir si il y en a besoin dans l'implémentation avec swarm

Edit : il existe un [exemple](https://docs.traefik.io/user-guides/docker-compose/basic-example/) dans la doc officielle sur lequel je ne suis pas passé..





























//

