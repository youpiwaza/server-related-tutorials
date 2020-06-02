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
- Using the other [DC syntax](https://docs.docker.com/compose/compose-file/#environment) and enforcing quotes

```bash
# TODO:

# ## Set up classic recommadations

# - custom db user, names, prefix

# ## Secrets/config

# [DH wp > Docker Secrets](https://hub.docker.com/_/wordpress/):

# `docker run --name some-wordpress -e WORDPRESS_DB_PASSWORD_FILE=/run/secrets/mysql-root`
```
