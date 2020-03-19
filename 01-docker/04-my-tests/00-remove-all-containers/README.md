# Simple commands to stop all running containers

Parce que des fois avec compose c'est relou

```bash
# Lister l'ensemble des containers qui tournent
# > docker ps -aq

# Lister l'ensemble des services qui tournent
# > docker service ls -q

# Arrêter et supprimer l'ensemble des services
> docker service rm $(docker service ls -q)

# Arreter l'ensemble des containers indépendants
> docker stop $(docker ps -aq)

# Puis, après quelques secondes, détruire l'ensemble des containers indépendants
> docker rm $(docker ps -aq)

# Puis, full clean
> docker system prune
# Valider
# > y
```
