# Création et implémentations de la clé SSH pour la première connexion

1. Générer les clés (privée et publique),
2. Créer l'agent ssh local, y ajouter la clé privée
3. Ajouter la clé publique sur le serveur.

```bash
# (Optionnel) Supprimer les précédentes clés SSH locales
# Si même setup que moi
#   WSL > ~/.ssh/
#   Windows > Users/MON_USER/.ssh/

> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/08-ssh-key-generation-full/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'

# Lancer les commandes dans le fichier généré manual-commands.md
```
