# Traefik on production

Cure the mess before templating for final installation on production

## Main commands

```bash
# builder_guy / syslogs globaux
sudo tail -f /var/log/syslog

# docker_guy / Réseau attachable public, pour que les services soient connectés à traefik/internet
docker network create --driver=overlay --attachable traefik-public

# docker_guy / Create a named volume for logs (debug + access)
# Do steps described below in #### How to manage named volumes access rights

# Traefik + proxy
# docker stack deploy -c traefik17.yml traefik # KO stack peut pas privileged
# docker_guy / Lancement via docker compose, sans -d, afin de voir les logs (forcés en json-file)
# docker-compose -f traefik.yml up
# Scaling/replicas
docker-compose -f traefik.yml up --scale dockersocketproxy=2
# docker-compose -f traefik.yml up --scale traefik=2 # KO, as port 80 can be published to one instance only

# docker_guy / Stack de test, sur http://test.masamune.fr/
docker stack deploy -c hello.yml hello
docker stack deploy -c helloDeux.yml helloDeux
docker stack deploy -c helloSub.yml helloSub

# [Online to curl/browser http://test.masamune.fr/](http://test.masamune.fr/)
# [Online to curl/browser http://grafana.masamune.fr/](http://grafana.masamune.fr/)

# docker_guy / Vérifications attributions des containers
docker service ls

# docker_guy / Suppression des services & réseaux
docker-compose -f traefik.yml down
docker stack rm hello
docker stack rm helloDeux
docker stack rm helloSub
docker system prune
```

## TODO

1. ✅ Faire marcher, déjà
   1. Bad gateway 502 / connection refused / connect: permission denied / mes couilles
      1. Rajouter au service Traefik

```yaml
# BOTH traefik and services using traefik needs to specify network used as label

# Tell Traefik to get the contents provided by this service using that shared network.
- "--providers.docker.network=traefik-public"
```

   1. Configuration de Traefik : Les services doivent être créés ET ASSIGNÉS [ROUTEURS !?](https://community.containo.us/t/traefik-v2-0-does-not-work-for-me-with-any-other-port-other-than-80/1380/2)
   2. Le port du load balancer doit correspondre au port intérieur du conteneur du service, ET NON AU PORT SORTANT
      1. [Fucking life saver](https://stackoverflow.com/a/49418168/12026487)

```yaml
services:
  helloworld:
    deploy:
      labels:
        - "traefik.http.routers.helloworld.service=helloworld666" # Assignation du service déclaré
        # - "traefik.http.services.helloworld666.loadbalancer.server.port=8080" # NO
        - "traefik.http.services.helloworld666.loadbalancer.server.port=80"
    ports:
      - "8080:80"
```

1. 🌱 Résoudre les éventuels problèmes dans les logs
2. ✅ Alpha reorder
3. ✅ Comments
4. ✅ Proper renaming
   1. ✅ Nomenclature ports exterieurs services (pas de doublons) / Regarder pour gestion automatique
      1. Pas besoin de spécifier explicitement le port de sortie
   2. ✅ Nomenclature clients pour services et autres conneries traefik
      1. Tester conflits de noms si services muliples
      2. traefik_1            | {"level":"error","msg":"Router defined multiple times with different configurations in [hello-helloworld-ziecuama7f13gx12pg8vh11jt helloDeux-helloworld-g79k1r85gyzcuxv3v33k7lwnq]","providerName":"docker","routerName":"helloworld","time":"2020-05-08T14:37:31Z"}
      3. > Cf. nomenclature
5. ✅ Minor linting/tweaks
   1. ✅ Force bridge driver for socket network
   2. ✅ socket volume > force read only
   3. ✅ Activer l'encryptage du réseau d'accès à la socket [bret fisher stack example](https://github.com/BretFisher/dogvscat/blob/master/stack-proxy-global.yml)
   4. ✅ Lancer traefik as read only, cf bret ^
   5. ✅ Cap drop all + Cap_ADD "CAP_NET_BIND_SERVICE"
   6. ✅ Specific user > docker peon
      1. command traefik error: error while building entryPoint web: error preparing server: error opening listener: listen tcp :80: bind: permission denied
      2. // Specific unprivileged user needs access to ports < 1024
         - sysctls:
           - net.ipv4.ip_unprivileged_port_start: 0
   7. ✅ Traefik stats > Stats collection is disabled. Help us improve Traefik by turning this feature on :). More details [here](https://docs.traefik.io/contributing/data-collection/)
6. ✅ Résoudre problèmes divers
   1. ✅ healthcheck traefik > OK direct
   2. ✅ "traefik.http.routers.helloworld.entrypoints=web" ???
      1. WARN > No entryPoint defined for this router, using the default one(s) instead: [web]
      2. Vérifier pour https
      3. > Plus de trace dans les logs
7. ✅ Rajouter mes recos de sécurité
   1. ✅ Proxy
   2. ✅ Traefik
   3. ✅ Tests hello
8. 🌱 Répliques
   1. ✅ Tests hello
   2. ✅ Proxy
   3. ❌ Traefik / Published port 80 can be allocated to one container only (traefik + replicas = 2 containers)
      1. TODO: Fix ?
9. ✅ Test avec 2 services
10. ✅ Test sur sous dossier
11. ✅ Gestion des logs traefik (json + volumes > fichiers sur host)
    1. Docs
        1. [Official docs](https://docs.traefik.io/observability/logs/)
        2. [exemple](https://community.containo.us/t/502-bad-gateway-solved/2947)
        3. [Access logs](https://docs.traefik.io/observability/access-logs/)
    2. ~~/var/log/*~~
    3. Traefik's container > /home/traefik.log
    4. Stored inside a named volume 'logs-traefik' in /home/traefik.log
12. 🚀🚀🚀🚀🚀 [Manage access logs](https://docs.traefik.io/observability/access-logs/)
13. Ajout https
14. Cleaner repertoire home hecarim

## Docs

- [Secu TLS](https://medium.com/@zepouet/how-to-run-tr%C3%A6fik-as-non-privileged-user-4a824bc5cc0)
- [security reco](https://containo.us/blog/traefik-and-docker-a-discussion-with-docker-captain-bret-fisher-7f0b9a54ff88/)

ha-proxy a besoin de privileged, incompatible avec user namespaces.

- [Désactiver userns pour un container](https://docs.docker.com/engine/security/userns-remap/#disable-namespace-remapping-for-a-container)
- NOT SUPPORTED FOR DOCKER STACK DEPLOY
- docker compose > userns_mode: "host"

Listening on ports below 1024: the range of ports [0–1024] are privileged and thus require special capabilities. Either you can bind to port > 1024, or ensure you are able to grant the Linux Capability CAP_NET_BIND_SERVICEto Traefik.

Docker socket membership: the socket file in /var/run/docker.sock is owned by the root user, and a group named docker. The unprivileged user of Traefik must be part of the group docker to allow access to the Docker API.

## Curation

### KO / syslog > networkd-dispatcher[1006]: ERROR:Unknown interface index

```bash
networkd-dispatcher[1006]: ERROR:Unknown interface index 40 seen even after reload
networkd-dispatcher[1006]: WARNING:Unknown index 40 seen, reloading interface list
```

Some researches on google, didn't help

### KO / ha-proxy > Can't open server state file '/var/lib/haproxy/server-state': No such file or directory

- [github issue](https://github.com/Tecnativa/docker-socket-proxy/issues/4)
- [other issue](https://github.com/mesosphere/marathon-lb/issues/214)
- [official doc](https://www.haproxy.com/fr/blog/introduction-to-haproxy-logging/)

Solutions seems to install ~rsyslog and force logs into it, not much time to dig into it..

### Logs management

They first need to be generated inside the container, then passed to the host through a volume

```yaml
services:
  traefik:
    command:
      # Enable logs
      - "--log.filePath=/home/traefik.log"
      - "--log.format=json"
      - "--log.level=DEBUG"
      # - "--log.level=ERROR"
```

Note: Compatible with read_only: true.

#### How to manage named volumes access rights

As bind mount is a security risk, we'll use a [named volume](https://docs.docker.com/engine/reference/commandline/volume_create/).

As we are using user namespaces, the user inside the container is the docker_peon.

Rights must be set accordingly before, through the builder_guy:

```bash
# docker_guy
# Create a named volume
docker volume create logs-traefik \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='traefik reverse proxy' \
   --label fr.masamune.type='core'

# Traefik needs access to his /var/log/traefik.log
#     We're gonna mount the volume from a temp container and edit the volume rights here
#        target=/home >> We'll edit temp container's /home to edit the volume
#     Later, Traefik container's /var/log/ will be mounted on volume's /home
# Go inside the volume through an attached container, not using --user, so we're having root access inside the container
docker run \
   -it \
   --rm \
   --mount \
      source=logs-traefik,target=/home \
   --workdir /home \
   alpine \
   /bin/ash

# Inside the temp container, create the files that need to be edited
>> touch traefik-access.log
>> touch traefik-debug.log
# Set rights accordingly to container's user (remapped)
#     Use chown -R if you need full folder access from inside the container
>> chown 1003:1003 traefik-access.log
>> chown 1003:1003 traefik-debug.log

# Vérification
>> ls -la
# total 8
# drwxr-xr-x    2 root     root          4096 May  9 13:29 .
# drwxr-xr-x    1 root     root          4096 May  9 13:28 ..
# -rw-r--r--    1 1003     1003             0 May  9 13:29 traefik-debug.log
# -rw-r--r--    1 1003     1003             0 May  9 13:29 traefik-access.log
>> exit
```

Note that the docker_peon still can't execute traefik-*.log files nor create folders/files :)

#### Assign the volume to the traefik container

```yaml
services:
  traefik:
    # Assign the_docker_peon unprivileged user
    user: 1003:1003
    volumes:
      - type: volume
        read_only: false
        source: logs-traefik
        target: /home/

# Also needs to be defined in the top level volume key
#     https://docs.docker.com/compose/compose-file/#volumes
volumes:
  logs-traefik:
    # Use the existing volume, do not recreate one with a prefix
    external: true
```

Check everything is fine

```bash
# Check from the container point of vue
# Run the docker compose
docker-compose -f traefik.yml up

# From another terminal
# Get the container's name
docker container ls

# Go in (alpine bash)
docker exec -it tests_traefik_1 /bin/ash
>> cd home/
>> ls -la
>> vi traefik.log
>>>> :q
>> exit
```
