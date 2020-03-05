# Ansible project : modules usages

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

Bien générée en ligne (privée et publique), mais comment la récupérer et l'installer en local automatiquement ?

```yaml
- name: Create a 2048-bit SSH key for user volibear in ~volibear/.ssh/id_rsa
  user:
    name: volibear
    generate_ssh_key: yes
    ssh_key_bits: 2048
    ssh_key_file: .ssh/id_rsa
```

### Add sudo rights

Besoin d'un transfert de fichier [Ansible Files modules > template](https://docs.ansible.com/ansible/latest/modules/template_module.html#template-module)

Ajout de l'extension recommandée : Better Jinja, pour la syntaxe.

Il y a possibilité :

- d'utiliser les variables dans les templates
- de spécifier un dossier de destination
- de gérer l'utilisateur et les groupes du fichier
- de gérer les droits du fichiers
- de valider et annuler (`validate & backups`), ex : mise en place de clé SSH
- de spécifier le caractère de fin de ligne

```yaml
- name: "{{ user }} devient sudoer"
  template:
    dest: etc/sudoers.d/{{ user }}-sudoer
    src: templates/sudoers.j2
    # Utilisation d'une commande dédiée (visudo) qui permet de vérifier l'intégrité de ce genre de fichiers
    validate: "visudo -cf %s"
  when: user is defined
```

Et il y a donc également besoin du fichier de template concerné

```j2
{{ user }} ALL=(ALL:ALL) NOPASSWD: ALL
```

## Variables

[Ansible > User guide > Working w playbooks > Variables](https://docs.ansible.com/ansible/latest/user_guide/playbooks_variables.html)

Règles :

- Underscore uniquement, pas de tirets, de points ni d'espaces
- Pas de chiffres uniquement (ou commence par chiffre ?)

Définition en début de fichier :

```yaml
- name: Basic tasks
  vars:
    wtv: lavaleur
```

Puis utilisation via la syntaxe `{{ wtv }}`.

**Attention**, les variables doivent toujours être utilisées dans des chaînes de caractères

```yaml
- name: Create a new user
  user:
    name: "{{ wtv }}"
```

### Possibilité de syntaxe objet

Définition :

```yaml
foo:
  field1: one
  field2: two
```

Utilisation :

```yaml
foo['field1']
# ou alors
foo.field1
```

### Defining variables in files

[Ansible doc](https://docs.ansible.com/ansible/latest/user_guide/playbooks_variables.html#defining-variables-in-files)





TODO







### Utilisation du conditionnel

Possibilité d'effectuer une tâche uniquement lorsqu'une variable est définie, via `when`

```yaml
- name: Create a new user
  when: wtv is defined
```

**Attention**, pas d'accolades !

---

```yaml

```
