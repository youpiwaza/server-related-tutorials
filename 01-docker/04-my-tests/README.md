# Attention

Si ça merde, possibilité que les ports ne soient pas disponibles (autres conteneurs en cours d'éxécution).

Pour vérifier:

```bash
> docker container ls
# Si des conteneurs sont présents
> docker container stop LE_CONTENEUR
> docker container rm LE_CONTENEUR
```
