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

> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/02-basic-ansible-example/ansible
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

## Readaction

Use `ansible-lint` to prevent errors & warnings.
