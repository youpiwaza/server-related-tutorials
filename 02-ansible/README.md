# Fonctionnement de Ansible

## Installation

Projet git install-dev-env/03-ansible

Configurer ssh : ./01-configuration-ssh/

## Execution

Aller dans le dossier correspondant, lancer la commande ansible-playbook.

Note : Il faut parfois lancer la connexion SSH une fois avant, avec la pass phrase..

```bash
# WSL
# Reco SSH, si ssh configuré correctement
> ssh awesome
# Pass phrase

# Acceder au projet
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/02-basic-ansible-example/ansible
# Configurer l'agent SSH en local
> eval `ssh-agent`
> ssh-add ~/.ssh/masamune-ssh-key-ed25519-yay

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

## Readaction

Use `ansible-lint` to prevent errors & warnings.

## Configuration

[Ansible doc](https://docs.ansible.com/ansible/latest/reference_appendices/config.html)

Recommandé : Modifier `/etc/ansible/ansible.cfg`

Utiliser les valeurs *Ini key*

Valeurs booléennes : 0 ou 1

Mes modifications :

```ini
# Forcer l'affichage des couleurs dans le terminal
force_color=1
```
