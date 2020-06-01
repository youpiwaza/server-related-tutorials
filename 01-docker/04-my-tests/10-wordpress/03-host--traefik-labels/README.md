# Faire tourner WP sur test-wordpress.masamune.fr

Sur le serveur, publication sur un DNS via les labels traefik

Can check on [http://test-wordpress.masamune.fr](http://test-wordpress.masamune.fr) (no https) due to test port binding

## Execute as docker stack

Add the deploy stuff in wordpress-stack.yml.

Don't forget to shut down DC first (same binded ports..).

```bash
# on host, w. docker_guy
# copy wordpress-stack.yml..

# start
docker stack deploy -c wordpress-stack.yml test-wordpress

# from another terminal or use -d ^
curl https://test-wordpress.masamune.fr

# stop
docker stack rm test-wordpress
```

### Bitnami Wordpress exposes on ports 8080/8443

Don't forget to link the proper port to Traefik loadbalancer.

```bash
docker service ls
# ID                  NAME                            MODE                REPLICAS            IMAGE                      PORTS
# lumzf3ujt0qj        test-wordpress_mariadb          replicated          1/1                 bitnami/mariadb:10.3
# lleh9zr7zskt        test-wordpress_wordpress        replicated          1/1                 bitnami/wordpress:5        *:30004->8080/tcp, *:30005->8443/tcp
```

```yml
## Traefik is linked through loadbalancer
services:
  wordpress:
    deploy:
      labels:
        - "traefik.http.services.https_testWordpress_MasamuneFr_Wordpress_Service.loadbalancer.server.port=8080"
    ports:
      - '8080'
      - '8443'
```

## Traefik add reminder / ~ Bad gateway

Don't forget to properly link your services to Traefik public network:

1. Declaration in Traefik's lanels
2. **Declaration in container's networks**
3. Declaration in the global network tag

```yml
wordpress:
    deploy:
      labels:
        # Tell Traefik to get the contents provided by this service using that shared network.
        - "traefik.docker.network=core-traefik-public"
        # Allow internet exposition (publish on port 80)
        - "traefik.enable=true"

    networks:
      # Connect to traefik dedicated overlay network, allow reverse proxy
      - core-traefik-public

networks:
  core-traefik-public:
    # Connect to an existing network, do not recreate one with a prefix
    external: true
```
