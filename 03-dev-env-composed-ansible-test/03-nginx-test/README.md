# Test de l'environnement de developpement : installation d'un nginx alakon

Basé sur ce [boilerplate](https://github.com/youpiwaza/server-related-tutorials/tree/master/03-dev-env-composed-ansible-test/02-example-updated)

Juste besoin de docker qui tourne.

---

Le but ici est d'installer, faire tourner et valider un serveur web nginx.

En gros exploiter le côté playbook et vérifier que tout roule *bieng* via [ansible uri](https://docs.ansible.com/ansible/latest/modules/uri_module.html).

## Commandes de base

```bash
# Accéder au dossier, à adapter
# > cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/03-dev-env-composed-ansible-test/03-nginx-test

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
