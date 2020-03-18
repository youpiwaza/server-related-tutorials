# Changer d'utilisateur en cours de playbook

But: Utilisateur dédié pour docker

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/10-change-user-in-same-playbook/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```
