# Connexion à de multiples hôtes en série

Afin de permettre un minimum d'opérations avec root, notemment création d'un utilisateur & changement du port SSH par défaut, puis reconnexion.

Changer `/ansible/hosts` pour tester

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/06-multiples-hosts/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```
