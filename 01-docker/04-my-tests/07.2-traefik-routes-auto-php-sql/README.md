# Traefik / Routes automatiques avec projet php sql ~KO

.. A partir de l'exemple précédent. Petit test rapide pour faire tourner le projet php sql.

~KO ça marche random 1/2 (avec l'ensemble des tests.. Y compris sans la route custom)


---



## Principales commandes


```bash
# Création du réseau publique, attention --attachable
> docker network create --driver=overlay --attachable traefik-public

# Lancement de traefik sans swarm en arrière plan
> docker-compose -f traefik.yml up -d

# Création de containers alakon
> docker-compose -f who.yml up -d
> docker-compose -f hello.yml up -d

# Lancement du projet php sql
> docker-compose -f php-sql.yml up -d
```

Vérifications sur [http://whoami.localhost/](http://whoami.localhost/) et [http://hello.localhost/](http://hello.localhost/).

*Arrêt des containers*

```bash
> docker-compose -f traefik.yml down
> docker-compose -f who.yml down
> docker-compose -f hello.yml down
> docker-compose -f php-sql.yml down
> docker system prune
```



## Adapter le projet

Ajout du réseau publique, à web (nginx) & adminer uniquement.

Création du réseau public, lancement de traefik puis de php-sql.

// php-sql (adminer) > Port 8080 déjà alloué (UI traefik) > changement

*Vérifications*

- UI traefik OK, mais phpfpm & sql apparaissent également.
- [http://web.localhost/](http://web.localhost/) OK, [http://adminer.localhost/](http://adminer.localhost/) OK
  - Adminer connexion impossible : `SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo failed: Name does not resolve`
    - NOM DU SERVEUR = db_tartopaum
      - lelelelel
  - Adminer apparaît sur http://localhost:8081/ & http://adminer.localhost:8081/

Adminer KO puis up, et web OK puis KO, wts

Gros problèmes de cache (même avec disable dans inspecteur > network) > tout en nav privée

---

Même avec ça, c'est grave aléatoire, des fois ça marche, des fois non.

Connexion à la bdd impossible via adminer à cause du réseau publique ?

---

Essai avec swarm, a moitié la marde aléatoire également.

Ajout du réseau publique à toute la famille.. Bof

---

Essai sans ma règle custom > web KO wtv FUCK YOU

Essai sans swarm sans règle custom

1/2 KO

Pb cache traefik ? a priori non

---

Sam soule, je verrai plus tard lors de l'implémentation définitive de traefik en local