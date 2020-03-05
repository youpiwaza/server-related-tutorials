# Mise en place du SSH

Ansible ne tourne pas sans le SSH.

## Test de connexion par défaut (hébergeur)

Vérification de la connexion SSH en direct, via les identifiants fournis par l'hébergeur

```bash
> ssh MON_USER@169.169.169.169
# Donner le password
# Ok

# Note : si port déjà changé, utiliser
> ssh MON_USER@169.169.169.169 -p 6969
# .. avec le port correspondant

# Garder cette connexion ouverte le temps de faire des tests !
```

## Configuration de SSH

[Tuto grafikart, on se refait pas](https://www.grafikart.fr/tutoriels/ssh-686) pour comprendre les bases SSH, puis vérification sur la [doc officielle](https://www.ssh.com/ssh/keygen) afin de s'assurer que ce n'est pas obsolète ; dans notre cas ça l'es.

On va donc se baser sur la doc, ainsi que sur [cet article](https://medium.com/risan/upgrade-your-ssh-key-to-ed25519-c6e8d60d3c54), plus abordable (*mais en conservant les commandes de la doc !*).

Attention à ne pas confondre le bash WSL (sur votre bécanne) et le bash serveur.

Les clés SSH sont composées de deux fichiers : un contenant la clé privée (sur votre bécanne) et un contenant la clé publique, sur le serveur (le même suffixé .pub)

### WSL / Création et ajout de la clé SSH

Vous pouvez changer les différentes variables (nom de fichier, serveur, etc.)

Créez et stockez une [pass phrase](https://www.ssh.com/iam/password/generator) forte.

```bash
# Création de la clé en local (WSL)
> ssh-keygen -f ~/.ssh/id-ssh-key-ed25519 -a 100 -t ed25519 -C "masamune.code@gmail.com"
# Ajout de la pass phrase

# Copier la clé publique sur le serveur, sera ajoutée dans ~/.ssh/authorized_keys
> ssh-copy-id -i ~/.ssh/id-ssh-key-ed25519 MON_USER@169.169.169.169
# Mot de passe du serveur
```

### Serveur / Vérification de l'ajout de la clé SSH

```bash
# Vérifier la configuration, PubkeyAuthentication doit être true (par défaut)
#   https://www.ssh.com/ssh/sshd_config
> sudo nano /etc/ssh/sshd_config

# Vérifier l'ajout de la clé (par défaut dans le dossier de l'utilisateur courant)
> nano ~/.ssh/authorized_keys
# OK
```

### WSL / Connexion en une ligne

Ajout de notre clé à l'agent SSH

```bash
# Vérifier présence de l'agent SSH
> eval `ssh-agent`

# Ajouter la clé à l'agent
> ssh-add ~/.ssh/id-ssh-key-ed25519
# Pass phrase

# Vérification de l'ajout local
> ssh-add -l
```

Création d'un raccourci en local

```bash
# Créer/Modifier le fichier de config ssh local WSL
> nano ~/.ssh/config

# Y ajouter
Host awesome
  HostName 169.169.169.169
  User MON_USER
  IdentityFile ~/.ssh/id-ssh-key-ed25519
  IdentitiesOnly yes

# Remettre les bons droits sinon plus de connexion ssh du tout -_-"
> chmod 600 ~/.ssh/config
> chown $USER ~/.ssh/config

> ssh awesome
```

Test > Ok, connexion avec la passphrase la première, puis directe.

### WSL / Duplica de la clé SSH pour windows

Pour le fichier situé sur la bécanne, il devra être accessible pour Windows (généralement dans Users/MON_UTILISATEUR/.ssh/), ainsi que pour WSL (généralement dans ~/.ssh/).

En gros, le répertoire des clés ssh de windows est copié dynamiquement dans celui de WSL (lien linux), cf. [ce tuto](https://florianbrinkmann.com/en/ssh-key-and-the-windows-subsystem-for-linux-3436/)

```bash
# WSL
# Dossier utilisateur
> cd ~/

# Affichage des fichiers/dossiers cachés
> ls -a

# Création du lien vers les clés SSH windows
#> ln -s /mnt/c/Users/MON_USER/.ssh ~/.ssh

# Attention, si vous avez la même configuration WSL, pas de /mnt
> ln -s /c/Users/MON_USER/.ssh ~/.ssh

# Vérifier la disponibilité
> cd ~/.ssh
> ls -a
# Doit afficher le contenu du dossier windows, et donc les clés générées précédemment
```

### Serveur / Sécurités supplémentaires

On applique les recos de Grafikart

```bash
# Modification de la conf ssh du serveur
> sudo nano /etc/ssh/sshd_config

# Ajouter/Changer
Port 6969
PermitRootLogin no

# Puis relancer le service ssh
#   Si problème, voir plus bas
> sudo service ssh restart
```

Sur WSL, adapter le fichier de connexion

```bash
> nano ~/.ssh/config

# Ajouter le port
Host awesome
  HostName 169.169.169.169
  User MON_USER
  IdentityFile ~/.ssh/id-ssh-key-ed25519
  IdentitiesOnly yes
  Port 6969
```

Tests de connexions :

```bash
# Classique, en spécifiant le port
> ssh MON_USER@169.169.169.169 -p 6969
# Password
# Ok

# Sortir du serveur
>> exit

# Connexion 1 line
> ssh awesome
# (pass phrase)
# Ok
```

## Problèmes rencontrés

### WSL / Impossible de se reconnecter en ssh après manipulation

Erreur `Bad owner or permissions on /root/.ssh/config`.

Ce fichier doit avoir des permissions spécifiques (600) sinon [cela merdouille](https://serverfault.com/a/253314)

```bash
# Local WSL
> chmod 600 ~/.ssh/config
> chown $USER ~/.ssh/config
```

### Serveur / Problème sudo

```bash
# Online
> sudo service ssh restart
# sudo: unable to resolve host MON_USER: Name or service not known
```

Pour cela, il faut [corriger certains fichiers](https://askubuntu.com/questions/59458/error-message-sudo-unable-to-resolve-host-none).

## Ressources

- [SSH Keygen](https://www.ssh.com/ssh/keygen)
- [SSH Agent](https://www.ssh.com/ssh/agent)
- [SSHD config](https://www.ssh.com/ssh/sshd_config)
- [Article plus abordable](https://medium.com/risan/upgrade-your-ssh-key-to-ed25519-c6e8d60d3c54)
- [SSH config file](https://linuxize.com/post/using-the-ssh-config-file/)
- [SSH for WSL](https://florianbrinkmann.com/en/ssh-key-and-the-windows-subsystem-for-linux-3436/)
