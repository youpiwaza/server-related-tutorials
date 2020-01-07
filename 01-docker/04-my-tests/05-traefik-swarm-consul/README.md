# Traefik / Implementation avec docker swarm & consul

Edit de fin : Mise en place à partir du tuto [Docker rocks](https://dockerswarm.rocks/traefik/) > Traefik & consul (ainsi que leurs UI) se lancent après quelques tweaks, et j'ai réussi à faire apparaitre un service (whoami) dans l'interface de traefik.

Mais impossible d'obtenir une redirection (du service whoami) avec ce setup, et après 1 journée d'essai, je vais repartir de la doc traefik pure, et sur la dernière version.

Je laisse les tests au cas ou..

---

Pour tester : `docker-compose up`

Sources :

- *Fichier docker-compose.yml*
- *Dossier configs /config*
  - Contient également les configurations par défaut



## Principales commandes

Lancement en tant que service via [swarm](https://docs.docker.com/get-started/part4/)

```bash
> docker stack deploy -c docker-compose.yml swarm-traefik
```

Arrêt du service

```bash
> docker stack rm swarm-traefik
```

**Notes**

- Possibilité de lancer via `docker-compose up` pour avoir de meilleurs logs des différents containers.

**docker-compose CLI avancé**

```bash
# swarm scale service
> docker service scale swarm-traefik_whoami=2
```



## Tutoriel docker swarm rocks

Traefik v2.0+ n'a pas de guide pour swarm, et la doc à l'air bien longue :/

Je vais repartir du tuto [Docker rocks](https://dockerswarm.rocks/traefik/) qui à l'air plus concret et plus rapide à mettre en place (config. automatique à partir des labels Docker).

Rôles : 

- Traefik as a load balancer/proxy
- [Consul](https://www.consul.io/) (distributed configuration key/value store) to store configurations and HTTPS certificates.
  - Pas forcément nécessaire dans notre cas, mais au cas ou plus tard il y aura plusieurs nodes (workers)


### Création de la configuration

Créer des [variables d'environnement](https://docs.docker.com/compose/environment-variables/)..

- EMAIL=admin@example.com
  - *generation of Let's Encrypt certificates*
- DOMAIN=sys.example.com
  - *Sous domaines pour les UI : traefik.sys.example.com & consul.sys.example.com*
- USERNAME=admin
  - *HTTP Basic Auth for Traefik and Consul UIs*
- PASSWORD=changethis
  - *UIs pw*
- HASHED_PASSWORD=$(openssl passwd -apr1 $PASSWORD)
  - *openssl to generate the "hashed" version of the password*
- CONSUL_REPLICAS=0
  - *If you have a single node, you can set CONSUL_REPLICAS to 0, that way you will only have the Consul "leader", you don't need the replicas if you don't have other nodes yet*
- TRAEFIK_REPLICAS=1
  - *if you have a single node, you can set TRAEFIK_REPLICAS to 1:*

..  dans un [fichier dédié](https://docs.docker.com/compose/env-file/).

**Corrections**

Le ternaire (conditionnel "?" pour vérifier la présence des variables) ne fonctionne pas, on l'enlève..


### Création du réseau public

Ouvert vers l'extérieur et l'internet

```bash
> docker network create --driver=overlay traefik-public
```


### Fichier pour swarm

Copie du fichier .yml de la doc afin de tester (..avec la vielle version de traefik)

```bash
# Déploiement
> docker stack deploy -c traefik-consul.yml traefik-consul

# Vérification
> docker stack ps traefik-consul
```

Mais UI KO à cause de la configuration par défaut :)

Pas possible de [rajouter des sous-domaines directement à localhost](https://stackoverflow.com/questions/19016553/add-subdomain-to-localhost-url)

Ajout d'un alias à localhost dans `WLS > /etc/hosts` (via vi, peut importe) // KO

Utilisation de Hostman pour windaube // KO

```conf
# localhost alias to allow subdomains (dev env traefik & consul)
127.0.0.1       poney.com
127.0.0.1       traefik.poney.com
127.0.0.1       consul.poney.com
```


Trop galère de monter avec docker-compose, il manque plein de trucs > [logs swarm](https://stackoverflow.com/a/43867933/12026487), [docs](https://docs.docker.com/engine/reference/commandline/service_logs/)

```bash
# Récupérer le nom du service
> docker service ls

# Logs
> docker service logs -f {NAME_OF_THE_SERVICE}
```



# Abandon de ce projet, tests ci-dessous

TODO : Peut être virer les répliques le temps que ça tourne (la conf ne passe pas..)
TODO : Examen des logs swarm pour voir pourquoi ça tourne pas

Commandes usuelles
```bash
> docker stack deploy -c traefik-consul.yml traefik-consul
> docker stack ps traefik-consul
# Vérifier que les services sont lancés/arrétés
> docker service ls
> docker stack rm traefik-consul
```

Pas oublier de changer env DOMAIN
Ou essayer sans sous domaine

Pb https 443 ?



---


Essais alalkons

Retrait des répliques consul
Retrait des répliques traefik



consul-leader:
    deploy:
      labels:
        - traefik.frontend.rule=Host:consul.localhost
// https://consul.localhost/ // works, mais mauvais mot de passe

```bash
# Go dans le container consul
> docker exec -it traefik-consul_consul-leader.1.y8dla41ej36tde896fl6z0wft /bin/ash

# Afficher les variables d'environnement
>> printenv
# [...] HASHED_PASSWORD=$(openssl passwd -apr1 $PASSWORD) # lelelel

# sortie du container
>> exit

# Encodage du pass a la mano
> openssl passwd -apr1 pizza
# $apr1$cjCw8yx4$k5c8VGxK1EpI3rG3xZ6t20

# Relance des services + verif
# printenv ok, connexion au localhost KO
# Remplacement des variables d'environnement KO
```

[Doc traefik basic auth](https://docs.traefik.io/middlewares/basicauth/)

```yaml
# Declaring the user list
#
# Note: all dollar signs in the hash need to be doubled for escaping.
# To create user:password pair, it's possible to use this command:
# echo $(htpasswd -nb user password) | sed -e s/\\$/\\$\\$/g
labels:
  - "traefik.http.middlewares.test-auth.basicauth.users=test:$$apr1$$H6uskkkW$$IgXLP6ewTrSuBkTrqE8wj/,test2:$$apr1$$d9hr9HBB$$4HxwgUir3HP4EsggP/QNo0"
```

Essai en doublant les $ (pas sûr que ça soit la même version..) // OK !

Remplacement du pass pour traefik.localhost // OK mais met ~10 secondes a se lancer ?

Essai avec les répliques // ok

---

What's next // flemme d'implémenter le truc, essai avec whoami & mes exemples

```bash
> docker stack deploy -c docker-compose-whoami.yml whoami
```

Test sur [whoami.docker.localhost](http://whoami.docker.localhost/) // bad gateway sans traefik, 404 avec

Test projet php SQL > [localhost:8080](http://localhost:8080/) // s'affiche mais connexion bdd ko

Analyse des logs traefik

10.0.0.2 - - [07/Jan/2020:10:22:27 +0000] "GET / HTTP/1.1" 404 19 "-" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36" 3 "backend not found" "/" 0ms

Aucune trace de php-sql (pas de labels dans le projet non plus..)


---


> Lecture complète de la doc traefik...

// test avec [config docker](https://docs.traefik.io/providers/docker/) avec label sur service.. sur docker-compose-php-sql.yml // ko af

Relecture de dockerswarm rocks prixy advanced, et set up de whoami comme consul..

Param label : exposedByDefault

Expose containers by default through Traefik. If set to false, containers that don't have a traefik.enable=true label will be ignored from the resulting routing configuration.

Défaut à false dans notre cas.. Test avec ajout du label concerné sur les projets.


- traefik.frontend.rule=Host:whoami.localhost                   // redirection
- traefik.enable=true                                           // Autoriser traefik
- traefik.port=80                                               // [default whoami port](https://github.com/containous/whoami#flags)
- traefik.tags=traefik-public                                   // main Traefik proxy will only expose services with the traefik-public tag (using a parameter below), make the service have this tag too, so that the Traefik public can find it and expose it.
- traefik.docker.network=traefik-public                         // tell Traefik to get the contents provided by this service using that shared network.
- traefik.redirectorservice.frontend.entryPoints=http           // make the service listen to HTTP, so that it can redirect to HTTPS.
- traefik.redirectorservice.frontend.redirect.entryPoint=https  // make Traefik redirect HTTP trafic to HTTPS for the web UI.
- traefik.webservice.frontend.entryPoints=https                 // make the web UI listen and serve on HTTPS.
- sans auth

Relance du service whoami, **il apparait dans traefik (onglet docker)** mais riend ans consul. Test [whoami.localhost](https://whoami.localhost/) > 3 plombes a charger, bad gateway

Essai de spécification du port : 

ports:
- 80:80

> failed to create service whoami_whoami: Error response from daemon: rpc error: code = InvalidArgument desc = port '80' is already in use by service 'traefik-consul_traefik' (tppoveq9hi8arfmdilj6oo95s) as an ingress port

Ptet conflit de ports... changement pour le port 8069:80, test [http://localhost:8069/](http://localhost:8069/) // ok, ko en swarm

Changement de conf traefik pour logs `logLevel=DEBUG`

Ajout du network traefik-public a whoami


Tjr KO, ça me soule, je repart de traefik offi, dernière version, sans consul.















//

