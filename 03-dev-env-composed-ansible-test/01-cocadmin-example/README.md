# Création d'un environnement de developpement pour tester les recettes ansible

Basé sur la [vidéo](https://www.youtube.com/watch?v=yqLPUOsy-8M) et les sources de [cocadmin 🥤](https://gist.github.com/ttwthomas/017891e536f745dcbcc5d0bc160a2643), ainsi que sur le [repo recommandé](https://hub.docker.com/r/williamyeh/ansible/) des images ansible.

Le but est d'avoir un docker compose comprenant un conteneur Ansible, ainsi qu'un ou plusieurs conteneurs de tests (ubuntu ou autre) sur lesquels seront testés les recettes.

Ansible fonctionnant en SSH, les conteneurs seront connectés de cette manière.

Un `volume:bind` (/playbooks) est monté afin de pouvoir continuer à éditer les fichiers depuis l'extérieur.

J'ai également mis une [version actualisée ⚡️](https://github.com/youpiwaza/server-related-tutorials/tree/master/03-dev-env-composed-ansible-test/02-example-updated) en ligne, avec ansible:alpine et ubuntu:18.04.

## Commandes de base

```bash
# Accéder au dossier, à adapter
# > cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/03-dev-env-composed-ansible-test/01-cocadmin-example/

# Lancer l'environnement de test (Ansible + ubuntu)
> docker-compose up -d

# Accéder au shell du conteneur ansible
> docker-compose exec master bash

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

## Problèmes recnontrés

Problème de connexion

```bash
fatal: [webyay]: UNREACHABLE! => {"changed": false, "msg": "Failed to connect to the host via ssh: Permission denied (publickey,password).\r\n", "unreachable": true}
```

Solution : Ne pas oublier de définir les accès dans le fichier `hosts` :

```yaml
[all:vars]
ansible_ssh_user=root
ansible_ssh_pass=ansible
```
