# Ansible project : arborescence & modules usages

All modules & docs : [Ansible module index](https://docs.ansible.com/ansible/latest/modules/modules_by_category.html)

Straight extracted from [grafikart ansible tutorial](https://www.grafikart.fr/tutoriels/ansible-753) and updated/tested following the documentation

## Package installation

[Ansible Packaging modules > apt](https://docs.ansible.com/ansible/latest/modules/apt_module.html#apt-module)

### Install 1 package

Exemple installation de git

```yaml
- name: Ubuntu 1 package installation
  apt: name=git update_cache=yes
```

### Install multiple packages through list

Exemple installation de plusieurs packages

```yaml
- name: Ubuntu multiple package installations
  apt:
    cache_valid_time: 3600
    name: ['git', 'vim', 'htop', 'zsh']
    update_cache: yes
    state: latest
```

### Clean package installation stuff

```yaml
- name: Upgrade all packages to the latest version
  apt:
    name: "*"
    state: latest

- name: Remove useless packages from the cache
  apt:
    autoclean: yes

- name: Remove dependencies that are no longer required
  apt:
    autoremove: yes
```

## Users

[Ansible System modules > user](https://docs.ansible.com/ansible/latest/modules/user_module.html#user-module)

Possibilités de 

- rajouter, modifier, supprimer un utilisateur.
- gérer son/ses groupes/s (ajout/append)
- son shell de base
- Créer automatiquement une clé SSH
- Une date d'expiration

```yaml
- name: Create a new user
  user:
    comment: Grafikart tutorial
    name: volibear
    shell: /usr/bin/zsh
```

### Add SSH key

#### Add an existing key

Rappel création de clé SSH

```bash
# Local / WSL
> ssh-keygen -f ~/.ssh/volibear-ssh-key-ed25519 -a 100 -t ed25519 -C "volibear@gmail.com"
# pass phrase
> volibear

# Ajouter à l'agent SSH local
> eval `ssh-agent`
> ssh-add ~/.ssh/volibear-ssh-key-ed25519
# pass phrase
```

Puis tâche Ansible qui cible le fichier nouvellement créé

```yaml
- name: Set authorized key taken from file
  authorized_key:
    key: "{{ lookup('file', '~/.ssh/volibear-ssh-key-ed25519.pub') }}"
    state: present
    user: volibear
```

Test de connexion

```bash
# Local / WSL
> ssh volibear@169.169.169.169 -p 6969
# OK, and no password :)
```

#### Create a SSH key on the fly

But how to retreive it automatically ?

```yaml
- name: Create a 2048-bit SSH key for user volibear in ~volibear/.ssh/id_rsa
  user:
    name: volibear
    generate_ssh_key: yes
    ssh_key_bits: 2048
    ssh_key_file: .ssh/id_rsa
```

Still work, both keys generated online..
