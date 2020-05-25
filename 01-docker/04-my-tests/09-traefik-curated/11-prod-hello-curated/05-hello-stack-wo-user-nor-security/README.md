# tutum/helloworld custom user fix

Test with stack and a working traefik

Edit: OK without user nor security, backing-up after sh*t tons of adaptations.

## Named volume for hello > Logs

```bash
# Create named volume
docker volume create test-hello-logs \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='tutum/helloworld logs' \
   --label fr.masamune.type='test'

# Manage files & rights
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
    chown 1003:1003 ./nginx/access.log  && \
    touch ./nginx/error.log             && \
    chown 1003:1003 ./nginx/error.log      '

# hello volume verification
docker run \
    -it \
    --rm \
    --mount \
        source=test-hello-logs,target=/home \
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

# (start hello)
docker-compose -f helloX.yml up
# hello mount verification,a djust container name
docker exec -it tests_helloworld_1 /bin/ash
>> cd /var/log/
>> ls -la
```

### HelloDeux volumes

```bash
# Create named volume
docker volume create test-helloDeux-logs \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='tutum/helloworld logs' \
   --label fr.masamune.type='test'

# Manage files & rights
docker run \
   --rm \
   --mount \
      source=test-helloDeux-logs,target=/home \
   --workdir /home \
   alpine \
   /bin/ash -c 'touch php-fpm.log && chown 1003:1003 php-fpm.log \
    && mkdir nginx \
    && touch ./nginx/access.log && chown 1003:1003 ./nginx/access.log \
    && touch ./nginx/error.log  && chown 1003:1003 ./nginx/error.log'

# Verif / logs
docker run \
    -it \
    --rm \
    --mount \
        source=test-helloDeux-logs,target=/home \
    --workdir /home \
    alpine \
    /bin/ash

```
