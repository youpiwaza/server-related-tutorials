# WORKS

traefik copy.yml (wo security)
hello.yml
helloDeux.yml

Pas de conflits fifo a cause du montage commun des répertoires /tmp/

Faire attention:

- traefik
  - Définition du bon network dans les hellos (label traefik)
  - Définition du bon port INTERNE (loadbalancer...)
- Hellos
  - Monter les logs
  - NE PAS MONTER /TMP/ , sinon conflits FIFO lors de l'accès au même volume par différentes réplicas
  - nginx.conf > FAIRE SORTIR SUR PORT 8080 pour éviter priviledged ports < 1024
  - Conteneurs > ne pas binder les ports ("80:8080" > NO NO) > ports: 8080 (attribution auto port published)
    - Pas le port 80, suite a modif nginx.conf
  - Ne pas oublier config php
- volumes
  - Bidouiller dans /home ..
  - .. et ensuite monter la ou nécessaire
  - QUAND LANCE SANS USER, le conteneur CHOWN LES LOGS en root !!!!
- Uris
  - helloDeux > works ONLY on [http uri](http://grafana.masamune.fr/), httpS KO AF

## TODO

1. Tester avec user: 1003:1003
2. Rétablir sécurités traefik
3. Lint that sh*t
4. > ansible
5. Remettre https
