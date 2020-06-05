# Apply Wordpress containers best practices, and make good use of environnement variables

- 🔍 [DH wordpress official README](https://hub.docker.com/_/wordpress/)
- 🔍 [DH bitnami WP README](https://hub.docker.com/r/bitnami/wordpress/)
- 🔍 [DH bitnami mariadb README](https://hub.docker.com/r/bitnami/mariadb/)

## Environnement variables

Note: official wp & bitnami's **don't uses the same ENV variables**. Needs a diff.

### Diff

Check that all security measures have been implemented by bitnami's image:

*wp-config.php* related:

- OK / Salt generation
  - OK / AUTH_KEYS...
- OK / Debug set by default to false

*Database* related:

- KO / Change wordpress db prefix
  - WORDPRESS_TABLE_PREFIX available for mariadb

Other:

- KO / WORDPRESS_CONFIG_EXTRA
- KO / No mention for the use of secrets

### Implement Env. variables

- ✅ Adding all variables with defaults
- ✅ Theme/Alpha reorder
- ✅ Using the other [DC syntax](https://docs.docker.com/compose/compose-file/#environment) and enforcing quotes

## Set up classic recommadations

- MariaDB / *Random chars > No special chars nor symbols*
  - custom root user 16 chars
  - random root password 32 chars
  - random database name 32 chars
  - random table prefix 8 chars
  - custom user 16 chars
  - custom user password 16 chars
- WordPress
  - *Maximum length is ~60-64 but seems random, prefer using 50 chars.*
  - Username / 50 random chars
    - NO SPECIAL CHARS, seems to be ko with bitnami's
    - ~~[SO > wp username limitations](https://wordpress.stackexchange.com/a/99478)~~
      - ~~`sanitize_user() is used on the username at signup time so it should be alphanumeric characters plus _ space . – * and @.`~~
      - ~~*Random chars > **Use** special chars & symbols, **no $ ' "** !*~~
    - Don't forget to display user through "Firstname Name" in wp-admin > Users > Your profile
  - Password / 50 random chars
    - `Wordpress password recommandations, use : ! ? % ^ & ), EXCLUDE $ ' "`

Note: Increasing wp containers healtcheck checkup time (~30-40s, so increasing to 60s) to compensate for database setup.

Verifications after containers startup:

```bash
docker service logs test-wordpress_mariadb
docker service logs test-wordpress_wordpress
```

## Secrets/config

doc:

- [docker secrets](https://docs.docker.com/engine/swarm/secrets/)
  - [DC ref > secrets](https://docs.docker.com/compose/compose-file/#secrets)
- ~[DH wp > Docker Secrets](https://hub.docker.com/_/wordpress/):
  - `docker run --name some-wordpress -e WORDPRESS_DB_PASSWORD_FILE=/run/secrets/mysql-root`

Note: To update or roll back secrets more easily, consider adding a version number or date to the secret name. This is made easier by the ability to control the mount point of the secret within a given container.

### OK / Secret test

Test secret creation, mount & access through a test service

```bash
# Create a file containing da secret
nano mah-secret.txt
> "shh dis is secret"

# Create a docker secret
docker secret create test-da-secret mah-secret.txt

# Verify
docker secret ls
# ID                          NAME                DRIVER              CREATED             UPDATED
# okz4wlpnzt3b9t532kp1hhe02   test-da-secret                          5 seconds ago       5 seconds ago

# Apply secret to a ~~container~~ service
docker service create --name test-secret-container --secret test-da-secret alpine bin/ash -c 'while sleep 3600; do :; done'

## Test
# Get container name
docker container ls
# docker ps --filter name=test-secret-container -q

# Go into da container
docker exec -it CONTAINER_NAME /bin/ash
# docker exec -it test-secret-container.1.ea4wqyzvyjzmb2qy5281ggn80 /bin/ash

>> cat /run/secrets/test-da-secret
## OK
# shh dis is secret

# Clean
>> exit
docker service rm test-secret-container
docker secret rm test-da-secret
```

### KO / Secrets setup in DC files

[doc](https://docs.docker.com/engine/swarm/secrets/#build-support-for-docker-secrets-into-your-images).

Note: Docker secrets do not set environment variables directly. This was a conscious decision, because environment variables can unintentionally be leaked between containers (for instance, if you use --link).

Bitnami's WP & Mariadb currently [don't support secrets](https://github.com/bitnami/bitnami-docker-wordpress/issues/194) (ENV > *_FILE, like official wordpress).

Note: Loading sensitive vars from a secret & mounting them as ENV variables still represent a liability. We'll keep ENV vars for now.. Just keep 'em in a separate file/private github repo in the ansible scripts.

LATER: When/if it supports, implement it.
