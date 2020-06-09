# Manual steps > Prepare for automation

List various steps / good practices to execute after installation > classic process review in order to prepare for automation.

---

## WP-CLI doc

Small review of possibilities regarding [wp-cli](https://make.wordpress.org/cli/handbook/guides/quick-start/).

- Install/activate plugins
- Tout ce que propose l'admin :D

### Bitnami's image

- [doc > WP-CLI tool](https://hub.docker.com/r/bitnami/wordpress/).
- [WP-CLI all commands](https://developer.wordpress.org/cli/commands/)

- you need use the proper system user, daemon.
  - Note: not sure about that
    - Works as it is
    - ~KO using `docker exec --user daemon`

```bash
# Get wp container name
docker ps

## OK
docker exec CONTAINER-NAME wp help
# docker exec test-wordpress_wordpress.1.dbl6yqi1bj4o456x6hmczojmk wp help

## NAME
##   wp
##
## DESCRIPTION
##   Manage WordPress through the command-line.

## Manually remove akismet from already installed wp

# OK / WP-CLI install akismet plugin..
docker exec test-wordpress_wordpress.1.dbl6yqi1bj4o456x6hmczojmk wp plugin install akismet
# OK / .. and activate
docker exec test-wordpress_wordpress.1.dbl6yqi1bj4o456x6hmczojmk wp plugin activate akismet

# OK / Update all plugins
docker exec test-wordpress_wordpress.1.dbl6yqi1bj4o456x6hmczojmk wp plugin update --all
```

---

## Defaults

Bullet-proof nomenclature for all variables.

### Random string generator

Possibility to generate username, password, db user, etc.

🔍Docs:

1. ✅🌱 [Ansible vault](https://docs.ansible.com/ansible/latest/user_guide/vault.html)
2. ✅ [Ansible lookup password](https://docs.ansible.com/ansible/latest/plugins/lookup/password.html)
3. ✅💚 [SO > Use case](https://stackoverflow.com/questions/46732703/how-to-generate-single-reusable-random-password-with-ansible)

Will be tested in a dedicated project, cf. server-related-tutorials/02-ansible/14-password-generation .

### Sensitive information management

See if secrets can be implemented, as it doesn't seem to be the case for bitnami's.

### Defaults storage

Automate github private repository creation + upload a generated README.md file ?

## Users

🤖 Available in WP-CLI 🤖

### Generate a wp user for each client/publisher

Without super admin access.

### User display name

wp-admin > Users > Your profile > "Firstname Name"

Prevents displaying admin log in username.

## Change language

🤖 Available in WP-CLI 🤖

Settings > General > Site language

Adapt to customer's need.

## Remove unused themes

🤖 Available in WP-CLI 🤖

Can be done from Admin:

- Appearance > Themes > (Theme) Details > Delete

## Plugins

🤖 Available in WP-CLI 🤖

1. Install needed plugins
2. Update plugins if needed
3. Activate needed plugins
4. Remove unused plugins

Plugin list:

- Wordfence Security – Firewall & Malware Scan
- ~Sitemap generator

## Default pages

🤖 Available in WP-CLI 🤖

- Add secondary pages (credits, legal notices, sitemap)
- Add them to the secondary menu

## Post defaults

🤖 Available in WP-CLI 🤖

- Categories
- Permalinks structure

## Docker volumes backup

### Initial

Make a volume backup dump after all initial configuration.

### Scheduled

Make a script and add to CRON.

1. Temp container that generate an archive
2. Send to a back server
3. Manage rotation

### Manage backup restoration

Make script using a temp container to connect to a volume and use a specific archive file.

## Google analytics account

- Create
- Link to MonsterInsight plugin

---

## Admin > Tools > Site health

### Set wordpress scheme to https

Fixed in the wp-stack.yml file.

```yml
services:
  wordpress:
    environment:
      # Uri default scheme/config
      WORDPRESS_SCHEME: 'https'
```

### KO / Some php modules are missing

Edit: Example to enable php modules already is present on [doc > Extend this image](https://hub.docker.com/r/bitnami/wordpress/).

LATER: Enable php modules if needed

### KO / REST API error

Seems ok when [accessing manually](https://test-wordpress.masamune.fr/wp-json/wp/v2/users/1).

LATER: This seems time-consuming, lots of stuff to check. Fix later.

## Docker recos

### KO / Change custom user

LATER: Fix custom user, currently 1001, should be 1003. Edit DockerFile ?

### Add volume labels

Done.
