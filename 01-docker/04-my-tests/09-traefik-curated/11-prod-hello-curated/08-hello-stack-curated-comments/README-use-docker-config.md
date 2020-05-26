# Use docker config

Little README file to store research & use cases of docker config.

WARNING: **doesn't work with docker-compose**, only docker stack.

## Docs

- [how-docker-manages-configs](https://docs.docker.com/engine/swarm/configs/#how-docker-manages-configs)
- [CLI ref](https://docs.docker.com/engine/reference/commandline/config/)
- [docker-compose syntax](https://docs.docker.com/compose/compose-file/#configs)
- [Stack TLS certificate and stuff / wont use for now](https://docs.docker.com/engine/swarm/configs/#advanced-example-use-configs-with-a-nginx-service)
  - Probably to populate config through a swarm

To update or roll back configs more easily, consider **adding a version number or date to the config name**. This is made easier by the ability to control the mount point of the config within a given container.

To update a stack, make changes to your Compose file, then re-run `docker stack deploy -c <new-compose-file> <stack-name>`. If you use a new config in that file, your services start using them. Keep in mind that **configurations are immutable**, so you can’t change the file for an existing service. Instead, you create a new config to use a different file.

## Use case & syntax

[docker compose config > long syntax](https://docs.docker.com/compose/compose-file/#configs).

```yaml
version: "3.8"
services:
  redis:
    image: redis:latest
    deploy:
      replicas: 1
    configs:
      - source: my_config
        target: /redis_config
        uid: '103'
        gid: '103'
        mode: 0440
configs:
  my_config:
    file: ./my_config.txt
  # Created through CLI docker config create OR another stack
  my_other_config:
    external: true
```
