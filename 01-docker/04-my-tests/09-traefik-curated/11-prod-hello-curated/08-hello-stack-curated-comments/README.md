# tutum/helloworld all fixes, working and properly commented

Final product after 3 weeks of mental diarrhea.

See precedent stuff in 01-docker/04-my-tests/09-traefik-curated/11-prod-hello-curated/* for more insights.

## Notes & recommandations

### Stacks (hellos)

- Traefik labels
  - Enable **only** services to be published on the internet `"traefik.enable=true"`
  - Define the network used by traefik `"traefik.docker.network=core-traefik-public"`
  - Give routers, services & middle ware unique names, as it can cause conflicts/be reused
    - I recommand to prefix with sub domains, domains, subfolders
    - Ex: the router for the helloworld service for the domain test.masamune.fr > `https_testMasamuneFr_Helloworld_Router`
  - Explicitly define the **internal** port of the container `"traefik.http.services.https_testMasamuneFr_Helloworld_Service.loadbalancer.server.port=8080"`
- Don't forget to explicitly attach to the global network:

```yaml
services:
  helloworld:
    networks:
      - core-traefik-public`
```

- And define the use of the **external** network in the **global** network tag

```yaml
networks:
  core-traefik-public:
    # Connect to an existing network
    external: true
```

- If there is some config overload to do, don't bind mount, but use config
  - See [dedicated README](./README-use-docker-config.md)
- Custom user
  - Define the custom user
  - Get knowledge of the image behavior about custom user in dockerhub
  - If the container needs access to specific files and folders to run properly, use *named volumes* and **adjust file & folders rights** accordingly (ex: nginx /logs)
    - And define the use of this named volume as external in the **global** volume tag
    - Manage rights through a temp alpine container in /home, and then mount them where needed in the stack container.

```yaml
volumes:
  test-hello-logs:
    external: true
```

- Custom user internal ports
  - As the user is unpriviledged, it can't use a port below 1024 without been granted either
    - the capability `CAP_NET_BIND_SERVICE`
    - the `sysctls: net.ipv4.ip_unprivileged_port_start: 0` exception
  - **BUT** both are not compatible with `docker stack`, so use config to publish on other ports > 1024 to avoid the issue
    - Traefik will do the re-routing through the load-balancer label
  - **DON'T BIND THE PUBLISHED PORTS** as it will reserve this port, needs manual attribution, and can cause malfunctions/more config for replicas

```yaml
services:
  helloworld:
    ports:
      # no no
      # - "80:80"
      # - "15000:80"
      # - "15000-15500:80"
      # Internal port > 1024 to prevent usage of root restricted ports
      # - "15000:8080"
      # - "80:8080"
      # yes
      - "8080"

# no no
# PORTS
# 0.0.0.0:15000->80/tcp

# yes
# > docker service ls
# ID                  NAME                       MODE                REPLICAS            IMAGE                      PORTS
# zeo9jjz4emij        helloDeux_helloworldDeux   replicated          2/2                 tutum/hello-world:latest   *:30011->8080/tcp
# kfmu3i75rhua        hello_helloworld           replicated          2/2                 tutum/hello-world:latest   *:30010->8080/tcp
```

## Encountered errors

### syslog / Closed fifo

Be careful when mounting volumes: when using replicas, all replicas will used the same volumes and it might cause conflicts.

Got this one when mounting the nginx /tmp/ folder simultaneously to multiple replicas.

### syslog / fatal task error" error="task: non-zero exit

When deploying a stack, can have this in syslog :

```bash
fatal task error" error="task: non-zero exit (78)" module=node/agent/taskmanager node.id=5fl4gm3ngog3m8ezddov93yqc service.id=qfe2dcwurt6xhyx8sforrmbqc task.id=wodsd2hf7eehiai1cjx6954d9
```

Solution: try lunching via docker-compose (not detached), which might have more container setup logs.

```bash
# Nah
> docker stack deploy -c hello.yml hello
## Logs
# fatal task error" error="task: non-zero exit (78)" : ¯\_(ツ)_/¯

# Yeph
> docker-compose -f hello.yml up
## Logs
# Starting tests_helloworld_1 ...done
# Attaching to tests_helloworld_1
# helloworld_1  | [25-May-2020 12:52:45] NOTICE: [pool www] 'user' directive is ignored when FPM is not running as root
# helloworld_1  | [25-May-2020 12:52:45] NOTICE: [pool www] 'group' directive is ignored when FPM is not running as root
# helloworld_1  | tail: can't open '/var/log/nginx/access.log': Permission denied
# helloworld_1  | nginx: [alert] could not open error log file: open() "/var/log/nginx/error.log" failed (13: Permission denied)
# helloworld_1  | 2020/05/25 12:52:45 [emerg] 1#0: open() "/var/log/nginx/error.log" failed (13: Permission denied)
```

### Containers lunch / Permission denied

This one was tricky. It will happen when a container needs access to a specific file or folder, and is ran with a custom/specific user.

By default, most rights will be set to root, and lunching with a custom user will block creation/edit rights.

The solution is to mount a (named ; for stacks) volume, create needed files and folders, then chown their rights BEFORE mounting it in the container.

This can be acheived through a temp container, like so:

```bash
docker run \
   --mount \
      source=test-helloDeux-logs,target=/home \
   --rm \
   --workdir /home \
   alpine \
   /bin/ash -c '                           \
    touch php-fpm.log                   && \
    chown 1003:1003 php-fpm.log            '
```

The folder inside the temp container isn't relevant, it's mostly where it will be mounted in the desired container.

Note that files that are in specific folders (nginx > /var/log/nginx/error.log), or need to be created by the container (traefik > https/acme.json) will **need the folder chowned appropriately**.

```bash
# Inside named volume for nginx logs ~test-hello-logs
#   KO THIS WON'T WORK
# >> ls -la
# drwx------    2 root     root          4096 May 25 10:39 nginx
# >> cd nginx/
# >> ls -la
# -rw-r--r--    1 1003     1003          8879 May 25 13:51 access.log
# -rw-r--r--    1 1003     1003         10553 May 25 13:51 error.log

# ---

#   OK
# >> ls -la
# drwxr-xr-x    2 1003     1003          4096 May 25 13:01 nginx
# >> cd nginx/
# >> ls -la
# -rw-r--r--    1 1003     1003          8879 May 25 13:51 access.log
# -rw-r--r--    1 1003     1003         10553 May 25 13:51 error.log
```

If you got any doubt, try accessing/creating/editing the concerned file through a custom user temp container:

```bash
docker run \
    -it \
    --mount \
        source=test-hello-logs,target=/home \
    --rm \
    --user 1003:1003 \
    --workdir /home \
    alpine \
    /bin/ash
>> touch stuff
>> vi stuff
```

#### Containers lunch / Permission denied / Cryptic containers hosrsh*t

Pay attention when testing volumes with different users. I ran into a weird scenario when:

1. I created a named container for nginx logs, and properly edited files/folders rights
2. Started the container with a custom user, and the mounted volume > OK > then stopped it
3. Started the same container WITHOUT a custom user (~as root inside the container), and the mounted volume > OK > then stopped it
4. Started the samecontainer with a custom user, and the mounted volume > KO

When the volumes' logs were accessed and edited by the rooted container, it **chowned them to root again**, throwing errors when re-used by the custom user.

#### Containers bounces up and down, and/or traefik going unhealthy

Don't bind services' containers ports. Only define the internal port, as it causes conflicts with traefik using the internal network 0.0.0.0 or some stuff.

```yaml
## docker stack deploy
#      syslog (docker daemon):
#      level=warning msg="Failed to allocate and map port 80-80: listen tcp 0.0.0.0:80: bind: address already in use"
#      level=error msg="WTV cleanup: failed to delete container from containerd: no such container"
#      level=error msg="Handler for POST /WTV/start returned error: container WTV: endpoint join on GW Network failed: driver failed programming external connectivity on endpoint gateway_97ff068b5ade (WTV): listen tcp 0.0.0.0:80: bind: address already in use"

## docker-compose up
#      ERROR: for tests_traefik_1  Cannot start service traefik: container WTV: endpoint join on GW Network failed: driver failed programming external connectivity on endpoint gateway_97ff068b5ade (WTV): listen tcp 0.0.0.0:80: bind: address already in use
#      ERROR: for traefik  Cannot start service traefik: container WTV: endpoint join on GW Network failed: driver failed programming external connectivity on endpoint gateway_97ff068b5ade (WTV): listen tcp 0.0.0.0:80: bind: address already in use

services:
  helloworld:
    ports:
      # no no
      # - "80:80"
      # - "15000:80"
      # - "15000-15500:80"
      # Internal port > 1024 to prevent usage of root restricted ports
      # - "15000:8080"
      # Especially this
      # - "80:8080"
      # yes
      - "8080"

# no no
# PORTS
# 0.0.0.0:15000->80/tcp

# yes
# > docker service ls
# ID                  NAME                       MODE                REPLICAS            IMAGE                      PORTS
# zeo9jjz4emij        helloDeux_helloworldDeux   replicated          2/2                 tutum/hello-world:latest   *:30011->8080/tcp
# kfmu3i75rhua        hello_helloworld           replicated          2/2                 tutum/hello-world:latest   *:30010->8080/tcp
```

## MISC

Volumes docs:

- [not-supported-for-docker-stack-deploy](https://docs.docker.com/compose/compose-file/#not-supported-for-docker-stack-deploy)
    See the section on how to configure volumes for services, swarms, and docker-stack.yml files.
    Volumes are supported but to work with swarms and services, they must be configured as named volumes or
    associated with services that are constrained to nodes with access to the requisite volumes.
- [share-data-among-machines](https://docs.docker.com/storage/volumes/#share-data-among-machines)
- [storage/tmpfs/](https://docs.docker.com/storage/tmpfs/)
- [!start-a-service-with-volumes](https://docs.docker.com/storage/volumes/#start-a-service-with-volumes)
- [!!volumes-for-services-swarms-and-stack-files](https://docs.docker.com/compose/compose-file/#volumes-for-services-swarms-and-stack-files)
- [SO / how-does-docker-swarm-implement-volume-sharing](https://stackoverflow.com/questions/47756029/how-does-docker-swarm-implement-volume-sharing)
