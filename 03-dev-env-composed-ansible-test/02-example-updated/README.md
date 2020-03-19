# Création d'un environnement de developpement pour tester les recettes ansible

Même chose mais **mise à jour** à date du 19/03/2020.

Docs:

- [Dockerize an SSH service](https://docs.docker.com/engine/examples/running_ssh_service/)
- [William Yeh's ansible images](https://hub.docker.com/r/williamyeh/ansible/)
- [Rastasheep ubuntu 18.04 sshd images](https://hub.docker.com/r/rastasheep/ubuntu-sshd) / [Dockerfile](https://github.com/rastasheep/ubuntu-sshd/blob/master/18.04/Dockerfile)

Mises à jour:

- **Master/ansible**
  - Passage sur la version alpine
  - Utilisation du gestionnaire de paquets recommandé [apk](https://wiki.alpinelinux.org/wiki/Alpine_Linux_package_management)
- **Serveur/ubuntu**
  - Utilisation de la version de [ubuntu](https://hub.docker.com/_/ubuntu) souhaitée / 18.04
  - Optimisation de la création de l'image (Un seul RUN)

---

Basé sur la [vidéo](https://www.youtube.com/watch?v=yqLPUOsy-8M) et les sources de [cocadmin 🥤](https://gist.github.com/ttwthomas/017891e536f745dcbcc5d0bc160a2643), ainsi que sur le [repo recommandé](https://hub.docker.com/r/williamyeh/ansible/) des images ansible.

Le but est d'avoir un docker compose comprenant un conteneur Ansible, ainsi qu'un ou plusieurs conteneurs de tests (ubuntu ou autre) sur lesquels seront testés les recettes.

Ansible fonctionnant en SSH, les conteneurs seront connectés de cette manière.

Un `volume:bind` (/playbooks) est monté afin de pouvoir continuer à éditer les fichiers depuis l'extérieur.

## Commandes de base

```bash
# Accéder au dossier, à adapter
# > cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/03-dev-env-composed-ansible-test/02-example-updated

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
# Vérifier que git n'est pas installé
# >>> git --version
# >>> -bash: git: command not found
# Resortir du conteneur de test
# >>> exit

# Depuis le conteneur ansible, lancer l'exécution du playbook, ici installation de git
>> ansible-playbook -i hosts playbook.yml

## Vérifier l'installation
# Se connecter au conteneur de test
>> ssh webyay
# Tester la présence de git
>>> git --version
# Si tout est OK
# >>> git version 2.7.4

# Sortir des conteneurs
>>> exit
>> exit

# Détruire les conteneurs
> docker-compose down
```

## Divers

### Forcer la re-création des images Docker

```bash
# > docker build - < master.Dockerfile
# > docker build - < server.Dockerfile
> docker-compose build
```

### Identifiants

Les identifiants de base du serveur de test sont user: `root` et pass: `ansible`.

Note: En cas de **changement des identifiants** (dans server.Dockerfile), ne pas oublier de les changer dans `/playbooks/hosts`.

## Problème au build des images

`apt-get update -y` qui ne passe pas, uniquement pour ubuntu 18:04 (tout bon sur 16:04)

```bash
E: Release file for http://archive.ubuntu.com/ubuntu/dists/bionic-updates/InRelease is not valid yet (invalid for another 9h 34min 24s). Updates for this repository will not be applied.
```

Peut être une erreur d'horloge, cf. [Stackoverflow](https://askubuntu.com/questions/1059217/getting-release-is-not-valid-yet-while-updating-ubuntu-docker-container)

> **Redémarrer pc**..

Si toujours KO, (ou Docker qui plante..), réinstaller/paramètres d'usine Docker, et forcer son restart.
