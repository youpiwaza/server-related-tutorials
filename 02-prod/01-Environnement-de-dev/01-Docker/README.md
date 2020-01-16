# Installation de Docker et ses potes sur windows

Durée : ~30mn-1h.

**Attention** : Même si maintenant c'est rarement un problème, vérifier que vous avez quelques Go de disponibles sur le disque d'installation.

1. Installation du [Linux pour Windows](https://docs.microsoft.com/en-us/windows/wsl/install-win10)
2. Installer [Docker desktop](https://docs.docker.com/docker-for-windows/install/)

## Faire marcher Docker et ses potes avec WSL

En gros, WSL ne peut pas installer le Docker daemon. Mais on le connecte à celui de Docker desktop :)

Basé sur cet [excellent article](https://nickjanetakis.com/blog/setting-up-docker-for-windows-and-wsl-to-work-flawlessly), dont je recommande la lecture avant de procéder à l'installation. Puis suivre les instructions ci-dessous car il y a des pétouilles.

**Ouksé** / Les opérations suivantes se feront sur le terminal Ubuntu de WSL (pas le terminal ni le powershell windows).

### Installation de Docker

Installation de docker (ce), utiliser la [documentation officielle](https://docs.docker.com/install/linux/docker-ce/ubuntu/), qui est maintenue.

*Notes* :

- Je pars sur la version stable
- Tout faire jusqu'a `sudo docker run hello-world` qui ne marchera pas > `docker: Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?.`
  - Ce qui est normal, car le docker daemon n'est pas (encore) connecté

Ajouter `sudo usermod -aG docker $USER` (dernières lignes de commandes de l'installation docker dans le tuto).

### Installation de Docker compose

Suivre [cette installation](https://stackoverflow.com/questions/36685980/docker-is-installed-but-docker-compose-is-not-why/36689427#36689427) recommandée plutôt que celle (via pip) de l'article.

### Suivre la suite du tutoriel

On peut ensuite effectuer les [dernières actions](https://nickjanetakis.com/blog/setting-up-docker-for-windows-and-wsl-to-work-flawlessly#install-docker-compose) du tutoriel.

## Vérifications

```bash
# Tester la connexion avec docker
> docker info

# Tester l'installation de docker compose
> docker-compose --version

# Lancer un conteneur
> docker run hello-world

```

### Docker build & run

```bash
# Création d'un build depuis un repo git
>  docker build \
  -t some-repo-content-nginx \
  https://github.com/youpiwaza/test-min-static-site.git#master:site

# Lancement du container
> docker run -d \
  --name test-repo-nginx  \
  -p 8081:80  \
  some-repo-content-nginx
```

Test sur [localhost:8081](http://localhost:8081/)

```bash
# Arrêt du container
> docker container stop test-repo-nginx
> docker container rm test-repo-nginx
```

### Docker compose

Récupérer un [exemple](https://github.com/youpiwaza/server-related-tutorials/tree/master/01-docker/04-my-tests/02-compose-nginx-php).

```bash
# Aller dans le dossier ou l'exemple se situe
> cd WTV/02-compose-nginx-php/
> docker-compose up -d
```

Test sur [localhost:8080](http://localhost:8080/)

```bash
# Arrêter
> docker-compose down
```

### Docker swarm

Possibilité de réutiliser l'exemple précédent :

```bash
# Monter le service
> docker stack deploy -c docker-compose.yml test-swarm
```

Test sur [localhost:8080](http://localhost:8080/)

```bash
# Arrêter
> docker stack rm test-swarm
```

### Cleaner

Supprimer les builds, containers arrêtés. & éventuels networks.

```bash
> docker system prune
```

## Problèmes rencontrés

### Impossible de se connecter à Docker

Attention ! Il y a un petit temps de latence lors du démarrage de votre bécane, docker desktop doit lancer le daemon avant que ce dernier soit accessible.

*C'est très con mais je me suis fait avoir une paire de fois après un reboot..*

Vérifiez dans votre barre des tâche que le daemon n'est pas en cours de lancement (animation sur l'icône + texte au survol).

Une notification windows apparaît quand il est lancé normalement.

### Impossible de se connecter à Docker 2

La manipulation `echo "export DOCKER_HOST=tcp://localhost:2375" >> ~/.bashrc && source ~/.bashrc` peut merdouiller.

Essayer de rajouter directement :

```bash
> export DOCKER_HOST=tcp://localhost:2375

# Vérifications
> echo "Docker Host is set to ${DOCKER_HOST}"
> docker info
```
