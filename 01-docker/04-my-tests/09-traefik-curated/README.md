# Traefik / Implementation avec docker swarm

Petite repasse dessus pour se remettre dans le bain, appliquer les nouvelles recommandations de sécurité apprises, vérifier les mises à jour des images et préparer à la version finale en vue de l'intégration sur le serveur.

Et cleaner un peu le bordel des anciens tests :')

## Commandes de base

```bash
# Acceder au projet
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/01-docker/04-my-tests/09-traefik-curated

# Autoriser connexion a docker via WLS
> export DOCKER_HOST=tcp://localhost:2375

# Initier docker swarm
> docker swarm init


## Exemple 1 / Base stacks traefik + hello world
> cd 01-basic-traefik-swarm
# Créer un réseau overlay public, différent de ingress (defaut docker)
> docker network create --driver=overlay traefik-public

# Déploiement de traefik & hello world
> docker stack deploy -c traefik.yml traefik && docker stack deploy -c hello.yml hello
# Tests & admin
# hello.localhost
# localhost:8080

# Suppression de l'exemple
> docker stack rm traefik && docker stack rm hello && docker network rm traefik-public

```

## Exemple de base

/01-basic-traefik-swarm

Implémentation d'une base Traefik avec Docker swarm :

- Monter une stack Traefik
- Monter une stack hello world avec adresse spécifiée en labels
