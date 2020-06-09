# Manual steps > Prepare for automation

List various steps / good practices to execute after installation > classic process review in order to prepare for automation.

- Add custom plugins
- Add secondary pages (credits, legal notices, sitemap)

## User display name

wp-admin > Users > Your profile > "Firstname Name"

Prevents displaying admin log in username.

## Change language

Settings > General > Site language

Adapt to customer's need.

## Remove unused themes

Can be done from Admin:

- Appearance > Themes > (Theme) Details > Delete

## Plugins

1. Install needed plugins
2. Update plugins if needed
3. Activate needed plugins
4. Remove unused plugins

Plugin list:

- Wordfence Security – Firewall & Malware Scan
- ~Sitemap generator

## Default pages

Add secondary pages (credits, legal notices, sitemap)

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
