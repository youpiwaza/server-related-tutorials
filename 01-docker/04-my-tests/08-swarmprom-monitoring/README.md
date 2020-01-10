# Swarmprom monitoring

Test de la suite de [dockerswarm rocks](https://dockerswarm.rocks/swarmprom/)



## Principales commandes

```bash
> git clone https://github.com/stefanprodan/swarmprom.git
> cd swarmprom
> ADMIN_USER=admin \
ADMIN_PASSWORD=admin \
SLACK_URL=https://hooks.slack.com/services/TOKEN \
SLACK_CHANNEL=devops-alerts \
SLACK_USER=alertmanager \
docker stack deploy -c docker-compose.yml monit

# RM
> docker stack rm monit
```



## Mise en place

Activation des [metrics & experimental](https://docs.docker.com/config/thirdparty/prometheus/).

Test du bundle via [l'installation classique](https://github.com/stefanprodan/swarmprom).

Liste de liens : 

- [prometheus (metrics database)](http://localhost:9090/)
- [grafana (visualize metrics)](http://localhost:3000/)
- [alertmanager (alerts dispatcher)](http://localhost:9093/)
- [unsee (alert manager dashboard)](http://localhost:9094/)

oké



# Prise en main

## Prometheus


Trop rien dedans de base, [tuto docker](https://docs.docker.com/config/thirdparty/prometheus/)


### Configuration

Besoin d'une [conf spécifique pour Windows (docker desktop)](https://docs.docker.com/config/thirdparty/prometheus/#configure-and-run-prometheus) // Onglet docker desktop

Docker desktop > Settings > Daemon > Advanced > Ajouter..

```conf
{
  # [...]
  # "metrics-addr" : "127.0.0.1:9323", # No, need 0.0.0.0:9323 # https://github.com/stefanprodan/dockerd-exporter ?
  "metrics-addr" : "0.0.0.0:9323",
  "experimental" : true
}
```


Vérification de la conf `docker exec -it CONT_NAME /bin/ash` > `cat /etc/prometheus/prometheus.yml` > Configuré pour linux.

---

Récupération du projet

```bash
> git clone https://github.com/stefanprodan/swarmprom.git
```

Modification de `/conf/prometheus.yml`.

```yml
scrape_configs:
  - job_name: 'prometheus'
    static_configs:
      #- targets: ['localhost:9090']
      - targets: ['docker.for.win.localhost:9090']
```

Relance du bousin

```bash
> ADMIN_USER=admin \
ADMIN_PASSWORD=admin \
SLACK_URL=https://hooks.slack.com/services/TOKEN \
SLACK_CHANNEL=devops-alerts \
SLACK_USER=alertmanager \
docker stack deploy -c docker-compose.yml monit

# Maj uniquement
> docker stack deploy -c docker-compose.yml monit
```

Vérifier que tout est bon sur [http://localhost:9090/targets](http://localhost:9090/targets)


#### Fix dockerd-exporter

dockerd-exporter > DOWN > Bad gateway

[Doc image dockerd](https://github.com/stefanprodan/dockerd-exporter) > Conf docker metrics > ip 0.0.0.0

Plus d'erreur mais 0/0 up pour dockerd-exporter & node-exporter.

[SO / Modification de l'image dockerd-exporter](https://github.com/stefanprodan/swarmprom/issues/37#issuecomment-456373262)

```yml
services:
  dockerd-exporter:
    #image: stefanprodan/caddy
    image: stefanprodan/dockerd-exporter
```

OK / dockerd-exporter (1/1 up)

Grafana > Dashboards :

- Prometheus stats / OK
- Docker swarm services / OK
- Docker swarm nodes / KO


#### Fix node-exporter / Nope

```bash
> docker service ls

# monit_node-exporter      global              0/1                 stefanprodan/swarmprom-node-exporter:v0.16.0
```

Le service est créé mais ne démarre pas

Reco pour windows > https://github.com/martinlindhe/wmi_exporter

Flemme + peu d'interêt en local (1 seul node manager & worker)



### Use Prometheus

[UI](http://localhost:9090/graph) et [Doc](https://docs.docker.com/config/thirdparty/prometheus/#use-prometheus)

Joli graph mais pas plus avancé. En fait prometheus collecte, et grafana affiche.



## Grafana

Le truc en démo sur dockerswarm rocks.

[UI](http://localhost:3000/)

En haut, choisir dashboards

- Prometheus stats // Bof
- Docker swarm Nodes // KO windows
- Docker swarm Services // Yeah
  - **Permet de voir si des conteneurs ont une utilisation abusive de ressources..**

Menu > Alertes > How to add alert > Beh c'est sur le dashboard en fait LAUL

Dashboard Docker swarm Services > CPU usage by Service > (Header) > Edit

Nouvelle fenêtre > En bas > Onglet Alert > Create alert

Bon ça merdouille pas mal (tests KO > Pas de datas) et pas possible de sauvegarder la conf d'une alerte. A voir sur le serveur, mais pas mal de possibilités :

- Services KO (health checks)
- Abus de CPU, Mémoire, etc.
- Plus de place sur le disque (logs flood, Contenus clients, etc.)
- Services les plus gourmands
- Problème spam réseau
- Problème crash/reboot nodes / services
- Abus docker daemon
- Définition de notifications (emails / chan slack.. Voir si possibilité de sms ou push) en cas de problèmes.

Note, des [alertes de base](https://github.com/stefanprodan/swarmprom#configure-alerting) sont configurées ave le pakcage /o/



# Vérifications versions

Pas de maj sur le pack depuis 1 ou 2 ans (selon les versions), vérification dans le docker-compose.

- volumes à revoir
- images
  - stefanprodan/dockerd-exporter   > 1 an sans maj, je ne sais pas quelle image de prom est la base
  - google/cadvisor                 > DEPRECATED: New images will NOT be pushed. Please use gcr.io/google-containers/cadvisor instead. 
    - Déplacé sur google hub ou chp, payant/relou
    - L'image sur le projet est la dernière actualisée
  - grafana 5.3.4                   > https://hub.docker.com/r/grafana/grafana , dernière release 1 mois, dernière maj 32mn lol.
    - 5.3.4 est la dernière version stable, le reste est en beta (6.5.0-beta1)
    - Test avec la version 6.5.0 > KO Bad gateway, il doit manquer de la conf
  - alertmanager:v0.14.0            > https://hub.docker.com/r/prom/alertmanager/tags , dernière release 1 mois
    - v0.20.0 actuellement
    - Test avec la 0.20 // Ok a première vue..
  - unsee v0.8.0                    > https://hub.docker.com/r/cloudflare/unsee/tags
    - v0.9.2
    - Test > Config error
  - node-exporter v0.16.0           > https://hub.docker.com/r/prom/node-exporter/tags
    - v0.18.1
    - Pas de test, deja KO
  - prometheus:v2.5.0               > https://hub.docker.com/r/prom/prometheus/tags
    - v2.15.2
    - Test OK /o/
  - caddy 0.10.10                   > https://hub.docker.com/r/abiosoft/caddy/tags
    - Le webserveur pour les 4 UI (~=nginx), l'image contient la conf
    - abiosoft/caddy:1.0.3
    - Test KO




























//