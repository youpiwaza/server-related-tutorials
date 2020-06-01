# Traefik manual setup

Bash commands to set up network and volumes.

Dedicated network for website to serve to the internet through traefik.

Named volumes to allow proper rights management to allow custom users for containers.

Volumes files created & managed from a temp alpine container (ran as root) in his /home folder, but will eventually be mounted elsewhere.

## Network

```bash
# Create overlay network
docker network create \
  --attachable \
  --driver=overlay \
  --label fr.masamune.client='masamune' \
  --label fr.masamune.maintainer='masamune.code@gmail.com' \
  --label fr.masamune.project='reverse proxy / traefik / public swarm overlay attachable network to grant internet access' \
  --label fr.masamune.type='core' \
  core-traefik-public
```

## Named volumes

### Logs

#### Creation

```bash
# Create named volume
docker volume create core-traefik-logs \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='traefik reverse proxy' \
   --label fr.masamune.type='core'

# Rights configuration
docker run \
   -it \
   --mount \
      source=core-traefik-logs,target=/home \
   --rm \
   --workdir /home \
   alpine \
   /bin/ash -c '                           \
    touch traefik-access.log            && \
    chown 1003:1003 traefik-access.log  && \
    touch traefik-debug.log             && \
    chown 1003:1003 traefik-debug.log      '
```

#### Verifications

```bash
docker run \
    -it \
    --mount \
        source=core-traefik-logs,target=/home \
    --rm \
    --user 1003:1003 \
    --workdir /home \
    alpine \
    /bin/ash
>> ls -la
# -rw-r--r--    1 1003     1003             0 May 19 10:27 traefik-access.log
# -rw-r--r--    1 1003     1003             0 May 19 10:27 traefik-debug.log
```

### Https

#### Creations

```bash
# Create named volume
docker volume create core-traefik-https \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='traefik reverse proxy' \
   --label fr.masamune.type='core'

# Rights configuration
docker run \
   -it \
   --mount \
      source=core-traefik-https,target=/home \
   --rm \
   --workdir /home \
   alpine \
   /bin/ash -c '                   \
    mkdir https                 && \
    chown -R 1003:1003 https/      '
```

#### Verification

```bash
docker run \
    -it \
    --mount \
        source=core-traefik-https,target=/home \
    --rm \
    --user 1003:1003 \
    --workdir /home \
    alpine \
    /bin/ash
>> ls -la
# drwxr-xr-x    2 1003     1003          4096 May 19 10:27 https
```

### Running container verifications

```bash
# (Start treafik), ansible or
#     docker-compose -f /home/singed_the_docker_peon_9f3eqk4s9/core/reverse-proxy/traefik/traefik.yml up

# Gather container name
# docker container ls

# Traefik mount verifications
docker exec -it -rm tests_traefik_1 /bin/ash
>> cd home/
#     Check https mount
>> ls -la
# drwxr-xr-x    2 1003     1003          4096 May 19 10:37 https
# drwxr-xr-x    2 root     root          4096 May 19 10:27 logs
>> cd https/
>> ls -la
#     /https/acme.json should be generated automattically & 1003:1003
# -rw-------    1 1003     1003             0 May 19 10:37 acme.json

# Check logs mount
>> cd /home/logs/
>> ls -la
# -rw-r--r--    1 1003     1003          1899 May 19 10:40 traefik-access.log
# -rw-r--r--    1 1003     1003             0 May 19 10:27 traefik-debug.log
```

## Logs display

```bash
# Debug file live from inside the container
docker exec -it tests_traefik_1 /bin/ash -c 'tail -f home/logs/traefik-debug.log'
docker exec -it core-traefik_traefik_1 /bin/ash -c 'tail -f home/logs/traefik-debug.log'

# Debug from logs volumes (don't "-f")
docker run \
   -it \
   --mount \
      source=core-traefik-logs,target=/home \
   --rm \
   --user 1003:1003 \
   --workdir /home \
   alpine \
   /bin/ash -c 'tail /home/traefik-debug.log'

# Access file live from inside the container
docker exec -it tests_traefik_1 /bin/ash -c 'tail -f home/logs/traefik-access.log'
docker exec -it core-traefik_traefik_1 /bin/ash -c 'tail -f home/logs/traefik-access.log'

# Access from logs volumes (don't "-f")
docker run \
   -it \
   --mount \
      source=core-traefik-logs,target=/home \
   --rm \
   --user 1003:1003 \
   --workdir /home \
   alpine \
   /bin/ash -c 'tail /home/traefik-access.log'
```
