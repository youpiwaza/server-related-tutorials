# Effecter les actions avec un utilisateur autre que root

But: une fois les utilisateurs générés, changer d'utilisateur et désactiver la connexion de l'utilisateur root

Projet: Des erreurs à résoudre lors du lancement de tâches avec un autre utilisateur..

Note: Ne pas oublier d'ajouter l'agent ssh et la clé privée de l'utilisateur en question..

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/09-install-with-another-user/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

Note: Pas de problème détecté, peut être du à une ancienne maauvaise configuration de ssh..