# Nomenclature Traefik

Stack containers labels & ports

## Noms des services

Suffixés automatiquement, pas besoin de gestion.

## Ports

10000 / Ok
20000 / Ok
30000 / Ok

80 & 443 / HTTP & HTTPS access, reserved for Traefik
10000+ / Core services & stacks (monitoring, etc.)
15000+ / Tests
20000+ / Clients, each got a range of 100 ports

Pas besoin de spécifier le port (si publication automatique sur 80 OU un seul port ouvert ??).

Ne pas spécifier, laisser le bousin se gérer automatiquement

## Labels

Tests si conflits

- Les routeurs doivent être uniques, sinon KO
- Les services (eg. load balancers) doivent être uniques, SINON AFFICHAGE ALEATOIRE DU CONTENEUR parmis ceux présents sur le load balancer

Règles:

- (camelCase)
- Préfixer http ou https
  - http sera redirigé vers https systématiquement via traefik
- Préfixer du sous domaine  + nom du site + extension + "_"
- Préfixer du nom du service + "_"
- Type traefik
- (Index) si plusieurs services ou routeurs..

```yaml
- "traefik.http.routers.helloworld.rule=Host(`test.masamune.fr`)"
- "traefik.http.routers.helloworld.service=helloworld666"
- "traefik.http.services.helloworld666.loadbalancer.server.port=80"

# DEVIENT

- "traefik.http.routers.https_testMasamuneFr_Helloworld_Router.rule=Host(`test.masamune.fr`)"
- "traefik.http.routers.https_testMasamuneFr_Helloworld_Router.service=https_testMasamuneFr_Helloworld_Service"
- "traefik.http.services.https_testMasamuneFr_Helloworld_Service.loadbalancer.server.port=80"
```

## Core Named volumes

### logs-traefik

Contains Traefik both debug & access logs

- /home/logs/traefik-access.log
- /home/logs/traefik-debug.log
