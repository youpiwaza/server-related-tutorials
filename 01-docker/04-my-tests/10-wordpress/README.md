# Wordpress through docker

Dedicated test repo :)

## Dockerhub official images

- [WordPress](https://hub.docker.com/_/wordpress/)
  - [DockerFile](https://github.com/docker-library/wordpress/blob/8215003254de4bf0a8ddd717c3c393e778b872ce/php7.3/apache/Dockerfile)
- [bitnami WordPress](https://hub.docker.com/r/bitnami/wordpress/)
  - [DockerFile](https://github.com/bitnami/bitnami-docker-wordpress/blob/5.4.1-debian-10-r28/5/debian-10/Dockerfile)

Comparatif des deux:

- Bitnami tourne sous un debian light
- Bitnami fait des releases régulièrement, avec les packages mis à jour (prévient les CVE)
- Documentation images bitnami mieux fournie, plus claire & plus simple à mettre en place
- Wp officiel > Port mappés sur 80 & 443
  - **bitnami sur 8080 & 8443** (custom user > avoir priviledged ports)
- Bitnami a 11 plugins pré-installés
  - incl. un truc pour envoi des emails
- Pas de soucis pour ajout d'images / plugins

DockerFile:

- /!\ Par défaut bitnami utilise l'utilisateur 1001, correspond au builder_guy ?
- WP monte un volume /var/www/html
- Bitnami expose des ports fixes : 8080 8443

## Notes

- Préférer l'utilisation des secrets docker (aux variables d'environnement) pour la mise en place
- Voir pour l'envoi d'emails, qui nécessite de la configuration supplémentaire ? (Serveur/Traefik/WP php ?)

## TODO

- Vérifier si cela tourne avec un autre user
- Se renseigner sur WP-CLI
