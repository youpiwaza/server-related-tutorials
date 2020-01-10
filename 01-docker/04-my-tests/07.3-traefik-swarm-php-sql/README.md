# Traefik / avec projet php sql

Sans routes auto, utilisation des labels, à partir de l'exemple 06-... qui fonctionne.



## Principales commandes

```bash
# Créer le réseau externe à traefik (lien avec les autres services)
> docker network create --driver=overlay traefik-public

# déploiement de traefik & whoami
> docker stack deploy -c traefik.yml traefik

# Déploiement d'un hello world
> docker stack deploy -c hello.yml hello

# Déploiement du projet
> docker stack deploy -c php-sql.yml php-sql
```

Vérifications sur [http://hello.localhost/](http://hello.localhost/) et **TODO**

- Possibilité de vérifier les réplicas via whoami (changement d'ip lors du rechargement de la page)

Les adresses sont fixées dans les .yml dans `services:LE_SERVICE:deploy:labels` > `- "traefik.http.routers.LE_SERVICE.rule=Host(URL_DU_SERVICE)"`

*Arrêt du service*

```bash
> docker stack rm traefik
> docker stack rm hello
> docker stack rm php-sql

# Supprimer le réseau
> docker network prune
```



## Adaptation du projet

Ajout du réseau, ajout des labels (activation, url, port interne de sortie) :

```yml
    deploy:
      labels:
        - "traefik.enable=true"
        # http://ladmin.localhost/
        - "traefik.http.routers.adminer.rule=Host(`ladmin.localhost`)"
        - "traefik.http.services.adminer.loadbalancer.server.port=8080"
```

UI > Ok

Localhost > KO

Réactivation des options pas indispensables de traefik ( *instinct de dev* O_ô )

`--providers.docker.network=traefik-public`

Localhost OK >.> [http://lesite.localhost/](http://lesite.localhost/) & [http://ladmin.localhost/](http://ladmin.localhost/) mais l'admin déconne à balle (problèmes de session ?)

- Y compris sans traefik
- Y compris en compose

Retrait du réseau public > OK

Remise du réseau public, retrait des autres réseaux...

- Ok via compose
- Down via swarm


Juste marre.. Surtout les résultats aléatoires