# Ansible project : modules usages

All modules & docs : [Ansible module index](https://docs.ansible.com/ansible/latest/modules/modules_by_category.html)

Straight extracted from [grafikart ansible tutorial](https://www.grafikart.fr/tutoriels/ansible-753) and updated/tested following the documentation

## Usage

```bash
# WSL
# Reco SSH, si ssh configuré correctement
> ssh awesome
# Pass phrase

# Acceder au projet
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/05-basic-project/ansible
# Configurer l'agent SSH en local
> eval `ssh-agent`
> ssh-add ~/.ssh/masamune-ssh-key-ed25519-yay

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

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

Définition dans un fichier dédié (format .yml), recommandation arbo : dans /vars :

```yaml
---
user2: shaco
user3: amumu
user4: hecarim
...
```

Utilisation :

**Attention**, pas de premier / devant le chemin

```yaml
vars_files:
  - vars/main.yml
```

**Note**: If /vars/main.yml is defined in a specific role folder, it will be loaded automaticcally (seems to, cf. /roles/conditionnalTemplating/)

### Utilisation du conditionnel

Possibilité d'effectuer une tâche uniquement lorsqu'une variable est définie, via `when`

```yaml
- name: Create a new user
  when: wtv is defined
```

**Attention**, pas d'accolades !

## Roles / Refacto

[Ansible doc > User guide > Working w playbooks > Creating réusable playbooks > roles](https://docs.ansible.com/ansible/latest/user_guide/playbooks_reuse_roles.html)

Création d'une arborescence dédiée par rôle, ce qui équivaut à un type de tâche (par exemple un rôle qui regroupe tout ce qui concerne la création d'un utilisateur)

Exemple d'arborescence **obligatoire** :

```md
roles/
  common/
    tasks/
    handlers/
    files/
    templates/
    vars/
    defaults/
    meta/
```

Utilisation :

```yaml
# Au même niveau que tasks
roles:
  - user
```

**Attention !**, même s'il sont définis après des tâches dans playbook.yml,les rôles seront **éxécutés avant** !

## Manage folders and files

See /roles/foldersAndFiles

Note: Prefer templates/wtv.j2 instead of touch stuff.

## Edit a specific line in an existing file

Useful to tweak configuration files.

See /roles/editLineInFile

Alternative: Remove file, and use a j2 template.

## Make a task reusable

Create a specific .yml file in roles/theTask/ folder, and call it with `include` and ~~`with_items`~~.

cf. roles/conditionnalTemplating

Prefer the [use of loops](https://docs.ansible.com/ansible/latest/user_guide/playbooks_loops.html) instead od with_SMTHG, even if it won't be deprecated

The conditionnal is used in the templating file.

## Git manipulations

Created a [sample repository](https://github.com/youpiwaza/sample-text-file.git) containing a single txt file.

Then cloning it following the [Ansible documentation](https://docs.ansible.com/ansible/latest/modules/git_module.html).
