# tutum/helloworld custom user fix

Test with stack and a working traefik

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

---

```bash
# Create a named volume for nginx' /tmp/
docker volume create test-hello-tmp-folders \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='tutum/helloworld /tmp/ folders' \
   --label fr.masamune.type='test'

# Cf. nginx conf
docker run \
   --rm \
   --mount \
      source=test-hello-tmp-folders,target=/home \
   --workdir /home \
   alpine \
   /bin/ash -c ' \
   touch nginx.pid       && chown 1003:1003 nginx.pid            && \
   mkdir proxy_temp_path && chown -R 1003:1003 proxy_temp_path/  && \
   mkdir fastcgi_temp    && chown -R 1003:1003 fastcgi_temp/     && \
   mkdir uwsgi_temp      && chown -R 1003:1003 uwsgi_temp/       && \
   mkdir scgi_temp       && chown -R 1003:1003 scgi_temp/           '

# Verifications
docker run \
    -it \
    --rm \
    --mount \
        source=test-hello-tmp-folders,target=/home \
    --user 1003:1003 \
    --workdir /home \
    alpine \
    /bin/ash

>> ls -la
# drwxr-xr-x    2 1003     1003          4096 May 21 14:40 client_temp
# drwxr-xr-x    2 1003     1003          4096 May 21 14:42 fastcgi_temp
# -rw-r--r--    1 1003     1003             0 May 21 14:42 nginx.pid
# drwxr-xr-x    2 1003     1003          4096 May 21 14:42 proxy_temp_path
# drwxr-xr-x    2 1003     1003          4096 May 21 14:42 scgi_temp
# drwxr-xr-x    2 1003     1003          4096 May 21 14:42 uwsgi_temp

# Mount verifications
docker container ls
docker exec -it hello_helloworld.1.ukh4jrej19yep9tucr8dvzix5 /bin/ash
>> cd tmp/
>> ls -la
total 32
# drwxr-xr-x    7 root     root          4096 May 21 14:42 .
# drwxr-xr-x    1 root     root          4096 May 21 14:45 ..
# drwxr-xr-x    2 1003     1003          4096 May 21 14:40 client_temp
# drwxr-xr-x    2 1003     1003          4096 May 21 14:42 fastcgi_temp
# -rw-r--r--    1 1003     1003             2 May 21 14:45 nginx.pid
# drwxr-xr-x    2 1003     1003          4096 May 21 14:42 proxy_temp_path
# drwxr-xr-x    2 1003     1003          4096 May 21 14:42 scgi_temp
# drwxr-xr-x    2 1003     1003          4096 May 21 14:42 uwsgi_temp

# ---
# /tmp/yolo
# Create a named volume for nginx' /tmp/
docker volume create test-hello-tmp-yolo-folders \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='tutum/helloworld /tmp/yolo folders' \
   --label fr.masamune.type='test'

docker run \
   --rm \
   --mount \
      source=test-hello-tmp-yolo-folders,target=/home \
   --workdir /home \
   alpine \
   /bin/ash -c 'mkdir yolo && chown -R 1003:1003 yolo/'

# Verifications
docker run \
    -it \
    --rm \
    --mount \
        source=test-hello-tmp-yolo-folders,target=/home \
    --user 1003:1003 \
    --workdir /home \
    alpine \
    /bin/ash

>> ls -la
# drwxr-xr-x    2 1003     1003          4096 May 21 15:31 yolo

# Mount verifications
docker container ls
docker exec -it hello_helloworld.1.ukh4jrej19yep9tucr8dvzix5 /bin/ash
>> cd tmp/yolo/
>> ls -la
# total 32
# drwxr-xr-x    7 1003     1003          4096 May 21 15:36 .
# drwxr-xr-x    3 root     root          4096 May 21 15:31 ..
# drwx------    2 1003     1003          4096 May 21 15:36 client_temp
# drwx------    2 1003     1003          4096 May 21 15:36 fastcgi_temp
# -rw-r--r--    1 1003     1003             2 May 21 15:36 nginx.pid
# drwx------    2 1003     1003          4096 May 21 15:36 proxy_temp_path
# drwx------    2 1003     1003          4096 May 21 15:36 scgi_temp
# drwx------    2 1003     1003          4096 May 21 15:36 uwsgi_temp
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

# /tmp/yolo
# Create a named volume for nginx' /tmp/
docker volume create test-helloDeux-tmp-yolo-folders \
   --label fr.masamune.client='masamune' \
   --label fr.masamune.maintainer='masamune.code@gmail.com' \
   --label fr.masamune.project='tutum/helloworld /tmp/yolo folders' \
   --label fr.masamune.type='test'

docker run \
   --rm \
   --mount \
      source=test-helloDeux-tmp-yolo-folders,target=/home \
   --workdir /home \
   alpine \
   /bin/ash -c 'mkdir yolo && chown -R 1003:1003 yolo/'

# Verif / logs
docker run \
    -it \
    --rm \
    --mount \
        source=test-helloDeux-logs,target=/home \
    --workdir /home \
    alpine \
    /bin/ash


docker run \
    -it \
    --rm \
    --mount \
        source=test-helloDeux-tmp-yolo-folders,target=/home \
    --workdir /home \
    alpine \
    /bin/ash
```
