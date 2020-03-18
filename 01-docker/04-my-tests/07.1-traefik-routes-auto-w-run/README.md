# Traefik / Routes automatiques avec run & swarm

.. A partir de l'exemple précédent. Petit test rapide avec un réseau externe voir si ça peut marcher pour `docker run`.

## Principales commandes

```bash
# Création du réseau publique, attention --attachable
> docker network create --driver=overlay --attachable traefik-public

# Lancement de traefik sans swarm en arrière plan
> docker-compose -f traefik.yml up -d

# Création de containers alakon
> docker-compose -f who.yml up -d
> docker-compose -f hello.yml up -d

# run
> docker run -d \
  -P \
  --name whorun \
  --network traefik-public \
  containous/whoami

# swarm + répliques
> docker stack deploy -c who-swarm.yml who-replicas
```

Vérifications sur [http://whoami.localhost/](http://whoami.localhost/) et [http://helloworld3.localhost/](http://helloworld3.localhost/).

Arrêt des containers

```bash
> docker-compose -f traefik.yml down
> docker-compose -f who.yml down
> docker-compose -f hello.yml down
> docker container stop whorun
> docker container rm whorun
> docker stack rm who-replicas
> docker system prune
```

## Ajout du réseau

```yml
services:
  traefik:
    networks:
    - traefik-public

networks:
  # Needs v before
  # > docker network create --driver=overlay --attachable traefik-public

  traefik-public:
    external: true
```

Attention, pour tourner avec docker-compose, besoind e rajouter --attachable lors de la création du réseau public.

Traefik ok, UI ok, Routes KO.

Ajout du réseau aux autre containers

```yml
services:
  who:
    image: containous/whoami:latest
    networks:
      - traefik-public

networks:
  traefik-public:
    external: true
```

Route OK :)

## Création du container via run

```bash
> docker run -d \
  -P \
  --name whorun \
  --network traefik-public \
  containous/whoami
```

Vérif [http://whorun.localhost/](http://whorun.localhost/) // **OK /o/**

## Essai avec swarm

Parce qu'on est joueurs.

```bash
> docker stack deploy -c who.yml whoswarm
```

Vérif (traefik ui) [http://whoswarm_who.localhost/](http://whoswarm_who.localhost/) // **OK /o/**

### Avec repliques

```bash
> docker stack deploy -c who-swarm.yml who-replicas
```

Problème sur UI > Les 3 répliques ont la même adresse, même si [http://who-replicas_who.localhost/](http://who-replicas_who.localhost/) tourne (1 instance au pif ?).

TODO : ajuster routes auto ? // flemme, peu d'interêt
