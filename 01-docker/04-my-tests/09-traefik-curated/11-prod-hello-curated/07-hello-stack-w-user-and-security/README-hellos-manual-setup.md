# Hellos manual setup

## Named volumes

### Hello

#### Creation

```bash
# Create named volume
docker volume create test-hello-logs \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='tutum/helloworld logs' \
   --label fr.masamune.type='test'

# Manage files & rights
# nginx/ folder MUST be 1003:1003 owned too, not only files inside
docker run \
   --rm \
   --mount \
      source=test-hello-logs,target=/home \
   --workdir /home \
   alpine \
   /bin/ash -c '                           \
    touch php-fpm.log                   && \
    chown 1003:1003 php-fpm.log         && \
    mkdir nginx                         && \
    touch ./nginx/access.log            && \
    touch ./nginx/error.log             && \
    chown -R 1003:1003 nginx'
```

#### Verifications

```bash
docker run \
    -it \
    --rm \
    --mount \
        source=test-hello-logs,target=/home \
    --user 1003:1003 \
    --workdir /home \
    alpine \
    /bin/ash
>> ls -la
# drwxr-xr-x    2 root     root          4096 May 19 09:30 nginx
# -rw-r--r--    1 1003     1003          5707 May 19 10:10 php-fpm.log
>> tail php-fpm.log
>> cd nginx/
>> ls -la
# -rw-r--r--    1 1003     1003          2579 May 19 10:15 access.log
# -rw-r--r--    1 1003     1003          4714 May 19 10:15 error.log
>> tail access.log
>> tail error.log
```

### helloDeux

#### Creations

```bash
# Create named volume
docker volume create test-helloDeux-logs \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='tutum/helloworld logs' \
   --label fr.masamune.type='test'

# Manage files & rights
# nginx/ folder MUST be 1003:1003 owned too, not only files inside
docker run \
   --rm \
   --mount \
      source=test-helloDeux-logs,target=/home \
   --workdir /home \
   alpine \
   /bin/ash -c '                           \
    touch php-fpm.log                   && \
    chown 1003:1003 php-fpm.log         && \
    mkdir nginx                         && \
    touch ./nginx/access.log            && \
    touch ./nginx/error.log             && \
    chown -R 1003:1003 nginx'
```

#### Verification

```bash
# Verif / logs
docker run \
    -it \
    --rm \
    --mount \
        source=test-helloDeux-logs,target=/home \
    --user 1003:1003 \
    --workdir /home \
    alpine \
    /bin/ash
```

### Running container verifications

```bash
# (start hello)
docker stack deploy -c helloX.yml hello
# hello mount verification,a djust container name
docker exec -it GET_CONTAINER_NAME /bin/ash
>> cd /var/log/
>> ls -la
```

## Logs display

```bash
## Logs display
# Debug file live from inside the container
docker exec -it hello_helloworld.1.abbt5iqtxpi0ck9pgpads2cx9 /bin/ash -c 'tail -f /var/log/nginx/error.log'

# Debug from logs volumes (don't "-f")
docker run \
   -it \
   --rm \
   --mount \
      source=test-hello-logs,target=/home \
   --workdir /home \
   alpine \
   /bin/ash -c 'tail nginx/error.log'
```
