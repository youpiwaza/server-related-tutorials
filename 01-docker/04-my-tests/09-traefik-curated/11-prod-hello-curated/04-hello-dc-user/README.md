# tutum/helloworld custom user fix

/!\ Doesn't work on httpSSS://HOST-IP:80

/!\ KO AF Si on vire sysctl pour autoriser port 80 (rappel: sysctl pas dispo pour stack)

## Volume creation

```bash
# Create overlay network
docker network create --driver=overlay --attachable core-traefik-public

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
   /bin/ash -c 'touch php-fpm.log && chown 1003:1003 php-fpm.log && mkdir nginx && touch ./nginx/access.log && chown 1003:1003 ./nginx/access.log && touch ./nginx/error.log  && chown 1003:1003 ./nginx/error.log'

# Volume verification
docker run \
    -it \
    --rm \
    --mount \
        source=test-hello-logs,target=/home \
    --workdir /home \
    alpine \
    /bin/ash
>> ls -la

# Start hello through docker-compose, adjust file name/location
docker-compose -f hello.yml up

# Mount verification, once service is up / adjust container name (tests_helloworld_1)
docker exec -it tests_helloworld_1 /bin/ash
>> cd /var/log/
>> ls -la
>> vi php-fpm.log
>> cd nginx/
>> ls -la
>> vi error.log
>> vi access.log
>> exit
```

## Custom nginx.conf

Needs both custom user adjust AND tutum php-fpm config, cf. custom-user-and-tutum--nginx.conf
