# Traefik on production

Cure the mess before templating for final installation on production

## Main commands

```bash
# builder_guy / syslogs globaux
sudo tail -f /var/log/syslog

# docker_guy / Réseau attachable public, pour que les services soient connectés à traefik/internet
docker network create --driver=overlay --attachable traefik-public

# Traefik + proxy
# docker stack deploy -c traefik17.yml traefik # KO stack peut pas privileged
# docker_guy / Lancement via docker compose, sans -d, afin de voir les logs (forcés en json-file)
docker-compose -f traefik.yml up

# docker_guy / Stack de test, sur http://test.masamune.fr/
docker stack deploy -c hello.yml hello

# [Online to curl/browser http://test.masamune.fr/](http://test.masamune.fr/)

# docker_guy / Vérifications
docker service ls
# http://test.masamune.fr/

# docker_guy / Suppression des services & réseaux
docker stack rm traefik
docker stack rm hello
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

1. Résoudre les éventuels problèmes dans les logs
2. Alpha reorder
3. Résoudre problèmes divers
   1. healthcheck traefik
   2. "traefik.http.routers.helloworld.entrypoints=web" ???
      1. WARN > No entryPoint defined for this router, using the default one(s) instead: [web]
      2. Vérifier pour https
   3. Activer l'encryptage du réseau d'accès à la socket [bret fisher stack example](https://github.com/BretFisher/dogvscat/blob/master/stack-proxy-global.yml)
   4. Lancer traefik as read only, cf bret ^
   5. Cap drop all + Cap_ADD "CAP_NET_BIND_SERVICE"
4. Rajouter mes recos de sécurité
5. Nomenclature clients pour services et autres conneries traefik
6. Gestion des logs traefik (json + volumes > fichiers sur host), [exemple](https://community.containo.us/t/502-bad-gateway-solved/2947)
7. Répliques
8. Test avec 2 services
9. Test sur sous dossier
10. Ajout https
11. Cleaner repertoire home hecarim

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