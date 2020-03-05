# Configuration du serveur

Utilisation d'Ansible afin de configurer le nouveau serveur.

Possibilité de tout tester, avant de reset le serveur pour installation finale :)

*Note* : Ansible ne supporte pas les accents >.>

## Commandes

```bash
> ansible-playbook -i hosts playbook.yml

# Avec gestion des lignes & tabs
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'

# Avec gestion des lignes & tabs
# Delai
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g' | sed 's/\\t/\t/g'
```

## Mise à jour de la distribution et des packages

Lors de la première connexion :

```bash
29 updates can be installed immediately.
14 of these updates are security updates.

New release '19.10' available.
Run 'do-release-upgrade' to upgrade to it.
```

Recherche des [bonnes pratiques](https://www.jeffgeerling.com/blog/2018/ansible-playbook-upgrade-all-ubuntu-1204-lts-hosts-1404-or-1604-1804-etc) pour faire les mises à jour automatiquement.

Corrections :

- Conversion à la syntaxe recommandée par la [doc](https://docs.ansible.com/ansible/latest/modules/apt_module.html#apt-module)
  - Ajout des cleans de fin d'exemples
- Ajout d'un reboot avant `do-release-upgrade`, il était demandé
  - Utilisation de `do-release-upgrade -f DistUpgradeViewNonInteractive` afin de ne pas avoir à répondre aux questions ([réponses par défaut automatiques](https://www.linuxnix.com/ubuntu-linuxhow-to-do-unattended-os-release-upgrade/)).
  - Ajout d'un reboot conditionnel, seulement si besoin
- Gestion des [erreurs](https://docs.ansible.com/ansible/latest/user_guide/playbooks_error_handling.html), nécessaire ici pour do-release-upgrade, qui ne renvoie pas false, mais une chaîne de caractères.
  - Plutôt ddes [tests](https://docs.ansible.com/ansible/latest/reference_appendices/test_strategies.html)

Vérification manuelle

```bash
# Sur le serveur
> lsb_release -a

# No LSB modules are available.
# Distributor ID: Ubuntu
# Description:    Ubuntu 19.10
# Release:        19.10
# Codename:       eoan
```
