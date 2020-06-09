# Generate random passwords

But: Generate passwords, and re-use them in playbooks.

```bash
> cd ~/../c/Users/Patolash/Documents/_dev/server-related-tutorials/02-ansible/14-password-generation/ansible

# Lancer le playbook
> ansible-playbook -i hosts playbook.yml | sed 's/\\n/\n/g'
```

🔍Docs:

1. ✅🌱 ~~[Ansible vault](https://docs.ansible.com/ansible/latest/user_guide/vault.html)~~
2. ✅ [Ansible lookup password](https://docs.ansible.com/ansible/latest/plugins/lookup/password.html)
3. ✅💚 [SO > Use case](https://stackoverflow.com/questions/46732703/how-to-generate-single-reusable-random-password-with-ansible)

## 1 Generate password and re-use it multiple times

```yml
- name: Test / Generate random passwords
  # Define ~global var from generated local vars v
  set_fact:
      my_pass: '{{ pwd_alias }}'
  # Generation and gathering through vars > lookup
  vars:
    pwd_alias: "{{ lookup('password', './password1.txt length=15 chars=ascii_letters') }}"

# Use anywahere in playbook, from ~global
- debug:
    msg: '{{ my_pass }}'
```

## 2 Specific characters

See doc.

```yml
"{{ lookup('password', './wp-password.txt length=50 chars=ascii_letters,digits,!,?,%,^,&,)') }}"
```

## 3 Prefix

Possibility to prefix when defining ~global var.

Be careful as password in file won't be prefixed.

```yml
  set_fact:
    # Be careful as password in file won't be prefixed
    prefix_my_pass: 'da_prefix_{{ prefix_password_alias }}'
```
