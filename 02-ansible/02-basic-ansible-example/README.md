# Ansible

Tests d'Ansible sur le nouveau serveur !

Faire les tutoriels d'abord, puis je reset le serveur avant installation finale :)

## Scripts de base d'Ansible

Suivi des [tutoriels grafikart](https://www.grafikart.fr/tutoriels/ansible-753) (voir playlist), avec adaptation via Ansible, l'ensemble sera stocké dans ce repo [/ansible](/ansible).

Test du premier exemple : Installation de git, puis lancement du playbook

```bash
> ansible-playbook -i hosts playbook.yml
```

Connexion KO :

```bash
fatal: [169.169.169.169]: UNREACHABLE! => {"changed": false, "msg": "Failed to connect to the host via ssh: MON_USER@169.169.169.169: Permission denied (publickey,password).", "unreachable": true}
```

Documentation ansible `If necessary, add your public SSH key to the authorized_keys file on those systems.`, ce qui semble legit :>

## Mise en place du SSH

Trop, cf. le [readme dédié](01-configuration-ssh/README.md).

Modification du port par défaut dans `/ansible/hosts`.

Test, Droits KO :

```bash 
TASK [Installation de git] *********************************************************************************************************************************************************
fatal: [169.169.169.169]: FAILED! => {"changed": false, "msg": "Failed to lock apt for exclusive operation"}
```

Besoin de **droits supplémentaires** pour tout ce qui touche à apt, ajout de `become: yes`.

```bash
TASK [Installation de git] *********************************************************************************************************************************************************
ok: [169.169.169.169]
```