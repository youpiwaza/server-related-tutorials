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

# cf. différents sous dossiers
```

## Traefik concept simple

1. (DNS, transforme test.DOMAIN.COM vers IP.SE.RV.EUR:80)
2. Entrypoints (ports depuis internet, ex: port 80)
3. Envoie vers les routes
4. Qui envoient vers les services associés

## LATER

TODO:

1. Traefik dashboard / KO AF
2. Log rotation in named volume
3. Adjust time zone for logs
   1. Not available in [v2+](https://docs.traefik.io/observability/logs/)
   2. Was available in [1.7](https://docs.traefik.io/v1.7/configuration/logs/#time-zones)
   3. This is retarded
