# Test des actions locales

Effectuer des [actions sur la machine locale](https://docs.ansible.com/ansible/latest/user_guide/playbooks_delegation.html), dans le but d'automatiser la création des clés ssh.

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/07-local-action/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```
