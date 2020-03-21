# Andible playbook > Install docker and run a container

Note: Ce **projet est KO**, docker ne tourne pas ; même si l'installation semble correcte. A tester sur le véritable serveur.

Basé sur ce [boilerplate](https://github.com/youpiwaza/server-related-tutorials/tree/master/03-dev-env-composed-ansible-test/02-example-updated)

Juste besoin de docker qui tourne.

---

Le but ici est d'installer docker, le faire tourner, puis faire tourner une image nginx alakon

Basé sur :

- [La doc officielle](https://docs.docker.com/install/linux/docker-ce/ubuntu/#install-using-the-repository)
- [Un article orienté ansible](https://www.digitalocean.com/community/tutorials/how-to-use-ansible-to-install-and-set-up-docker-on-ubuntu-18-04)
- [Et son cookbook d'exemple](https://github.com/do-community/ansible-playbooks/blob/master/docker_ubuntu1804/playbook.yml)

## Notes

### Docker KO

L'installation est ok, mais comme je m'y attendait, Docker in Docker ne tourne pas (+ le fait que WSL se connecte à Docker desktop), même en

- Ouvrant le port 2375 sur l'image
- Spécifiant l'hôte Docker `export DOCKER_HOST=tcp://localhost:2375`
- [partageant la socket](https://jpetazzo.github.io/2015/09/03/do-not-use-docker-in-docker-for-ci/) via un volume.
  - Note: Pas de trace du dossier concerné dans WLS local, ce qui parait logique vu qu'on passe par Docker desktop..
- Passant le conteneur en mode *privileged* pour pouvoir utiliser *DinD*

```bash
# Conteneur ubuntu
>> root@7fcfb15359f0:/# docker info

>> Client:
>>  Debug Mode: false
>>
>> Server:
>> ERROR: Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?
>> errors pretty printing info
```

### PIP KO

pip est KO même

- malgré l'installation classique (pip3 fonctionne lorsque l'on se connecte en direct au conteneur ubuntu)
- en spécifiant l'exécutable dans ansible
- en installant les packages sur l'hôte (conteneur ansible)

## Commandes de base

```bash
# Accéder au dossier, à adapter
# > cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/03-dev-env-composed-ansible-test/04-docker-install-and-test

# Lancer l'environnement de test (Ansible + ubuntu)
> docker-compose up -d

# Accéder au shell du conteneur
# > docker-compose exec master bash
# Attention, shell alpine
> docker-compose exec master ash

# Depuis le conteneur ansible, vérifier la connexion SSH
# >> ssh webyay
# Accepter le certificat SSH
# >> yes
# Mot de passe root du conteneur ou seront exécutées les commandes Ansible, cf. server.Dockerfile
# >> ansible
# Resortir du conteneur de test
# >>> exit

# Depuis le conteneur ansible, lancer l'exécution du playbook, ici installation de git. En prenant en compte les retours à la ligne :)
>> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'

## Vérifier l'installation
# Sur le navigateur, aller sur localhost:8080/, le site doit s'afficher

# Sortir du conteneur
>> exit

# Détruire les conteneurs
> docker-compose down
```

## Divers

## Accéder au conteneur webyay

```bash
> docker-compose exec webyay bash  
```

### Forcer la re-création des images Docker

```bash
# > docker build - < master.Dockerfile
# > docker build - < server.Dockerfile
> docker-compose build
```

### Relancer le service web (remise à zéro du container)

Plutôt qu'utiliser scale, utiliser [restart](https://docs.docker.com/compose/reference/restart/).

```bash
> docker-compose restart webyay
```

Note: docker-compose scale est deprecated, il faut plutôt utiliser le [flag --scale](https://docs.docker.com/compose/reference/up/).

```bash
> docker-compose up --scale webyay=3
```

### Identifiants

Les identifiants de base du serveur de test sont user: `root` et pass: `ansible`.

Note: En cas de **changement des identifiants** (dans server.Dockerfile), ne pas oublier de les changer dans `/playbooks/hosts`.

## Manip suppélemntaires

Note: Le service web s'est vu rediriger vers le port 8080, afin de donner sur [localhost:8080](http://localhost:8080/).

```yaml
  webyay:
    # Envoyer le flux du serveur sur localhost:8080
    ports:
    - '8080:80'
```
